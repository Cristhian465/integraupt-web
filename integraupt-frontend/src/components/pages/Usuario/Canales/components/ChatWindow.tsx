import { useCallback, useEffect, useRef, useState } from "react";
import { Check, Download, FileArchive, FileSpreadsheet, FileText, Hash, Paperclip, Pencil, Reply, Send, Smile, Trash2, X } from "lucide-react";
import type { Canal, LinkPreview, Mensaje, Tema, TipoArchivoAdjunto } from "../types";
import {
  createTema,
  deleteTema,
  editarMensaje,
  eliminarMensaje,
  enviarMensaje,
  fetchEscribiendo,
  fetchLinkPreview,
  fetchMensajes,
  fetchTemas,
  marcarEscribiendo,
  toggleReaccion,
  uploadArchivo,
} from "../services/canalesService";

const EMOJIS = ["❤️", "👍", "😂", "😮", "😢", "👏"];
const URL_RE = /(https?:\/\/[^\s]+)/g;
const YOUTUBE_RE = /(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)([\w-]{11})/;
const ADJUNTO_ACCEPT = "image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.7z";
const USER_COLORS = ["#ef4444", "#f97316", "#eab308", "#65a30d", "#10b981", "#06b6d4", "#3b82f6", "#8b5cf6", "#d946ef", "#ec4899"];

function colorForUser(idUsuario: number): string {
  return USER_COLORS[Math.abs(idUsuario) % USER_COLORS.length];
}

function detectUrl(text: string): string | null {
  const m = text.match(URL_RE);
  return m ? m[0] : null;
}

function youtubeThumb(url: string): string | null {
  const m = url.match(YOUTUBE_RE);
  return m ? `https://i.ytimg.com/vi/${m[1]}/hqdefault.jpg` : null;
}

function extensionDe(nombre?: string | null): string {
  if (!nombre) return "";
  const i = nombre.lastIndexOf(".");
  return i >= 0 ? nombre.slice(i + 1).toLowerCase() : "";
}

function FileIcon({ nombre }: { nombre?: string | null }) {
  const ext = extensionDe(nombre);
  if (["zip", "rar", "7z"].includes(ext)) return <FileArchive size={22} />;
  if (["xls", "xlsx", "csv"].includes(ext)) return <FileSpreadsheet size={22} />;
  return <FileText size={22} />;
}

function describeAdjunto(tipo?: TipoArchivoAdjunto | null): string {
  if (tipo === "image") return "📷 Imagen";
  if (tipo === "video") return "🎥 Video";
  if (tipo === "audio") return "🎵 Audio";
  if (tipo === "file") return "📎 Archivo";
  return "";
}

function Adjunto({ tipo, url, nombre }: { tipo?: TipoArchivoAdjunto | null; url?: string | null; nombre?: string | null }) {
  if (!url) return null;
  if (tipo === "image") {
    return (
      <a href={url} target="_blank" rel="noreferrer">
        <img src={url} alt={nombre ?? "imagen enviada"} className="chat-img" />
      </a>
    );
  }
  if (tipo === "video") {
    return <video src={url} controls className="chat-video" />;
  }
  if (tipo === "audio") {
    return <audio src={url} controls className="chat-audio" />;
  }
  return (
    <a href={url} target="_blank" rel="noreferrer" className="chat-file-card">
      <FileIcon nombre={nombre} />
      <span className="chat-file-name">{nombre ?? "Archivo"}</span>
      <Download size={14} />
    </a>
  );
}

function formatHora(iso?: string | null): string {
  if (!iso) return "";
  return new Date(iso).toLocaleTimeString("es-PE", { hour: "2-digit", minute: "2-digit" });
}

function formatFecha(iso?: string | null): string {
  if (!iso) return "";
  return new Date(iso).toLocaleDateString("es-PE", { day: "numeric", month: "short" });
}

function getInitials(name?: string | null): string {
  if (!name) return "?";
  return name.split(" ").slice(0, 2).map((w) => w[0]).join("").toUpperCase();
}

function describeReplyText(r: { eliminado?: boolean; archivoUrl?: string | null; archivoTipo?: TipoArchivoAdjunto | null; contenido?: string | null }): string {
  if (r.eliminado) return "🚫 Mensaje eliminado";
  if (r.archivoUrl && !r.contenido) return describeAdjunto(r.archivoTipo);
  return r.contenido ?? "";
}

function LinkPreviewCard({ preview, ytThumb, fallbackUrl }: { preview: LinkPreview | null; ytThumb: string | null; fallbackUrl: string }) {
  if (!preview && !ytThumb) return null;
  const targetUrl = preview?.url ?? fallbackUrl;
  let host = targetUrl;
  try { host = new URL(targetUrl).hostname; } catch {}
  const image = preview?.image ?? ytThumb;
  return (
    <a href={targetUrl} target="_blank" rel="noreferrer" className="chat-link-preview">
      {image && (
        <div className="chat-link-preview-img-wrap">
          <img src={image} alt="preview" className="chat-link-preview-img" />
          {ytThumb && <span className="chat-link-preview-play">▶</span>}
        </div>
      )}
      <div className="chat-link-preview-body">
        <p className="chat-link-preview-site">{preview?.siteName ?? (ytThumb ? "YouTube" : host)}</p>
        {preview?.title && <p className="chat-link-preview-title">{preview.title}</p>}
        {preview?.description && <p className="chat-link-preview-desc">{preview.description}</p>}
      </div>
    </a>
  );
}

function MessageBubble({
  msg, isOwn, usuarioId, puedeModerar, onReply, onDelete, onToggleEmoji, onEdit,
}: {
  msg: Mensaje; isOwn: boolean; usuarioId: number; puedeModerar: boolean;
  onReply: (m: Mensaje) => void;
  onDelete: (m: Mensaje) => void;
  onToggleEmoji: (m: Mensaje, emoji: string) => void;
  onEdit: (m: Mensaje, nuevoContenido: string) => Promise<void>;
}) {
  const [showEmojis, setShowEmojis] = useState(false);
  const [editando, setEditando] = useState(false);
  const [editTexto, setEditTexto] = useState(msg.contenido ?? "");
  const actionsRef = useRef<HTMLDivElement>(null);
  const url = !msg.eliminado && msg.contenido ? detectUrl(msg.contenido) : null;
  const ytThumb = url ? youtubeThumb(url) : null;
  const [preview, setPreview] = useState<LinkPreview | null>(null);
  const avatarColor = colorForUser(msg.usuarioId);

  useEffect(() => {
    setPreview(null);
    if (!url) return;
    let cancelled = false;
    fetchLinkPreview(url).then((p) => { if (!cancelled) setPreview(p); }).catch(() => {});
    return () => { cancelled = true; };
  }, [url]);

  useEffect(() => {
    if (!showEmojis) return;
    const handler = (e: MouseEvent) => {
      if (actionsRef.current && !actionsRef.current.contains(e.target as Node)) setShowEmojis(false);
    };
    document.addEventListener("mousedown", handler);
    return () => document.removeEventListener("mousedown", handler);
  }, [showEmojis]);

  const iniciarEdicion = () => { setEditTexto(msg.contenido ?? ""); setEditando(true); };
  const guardarEdicion = async () => {
    if (!editTexto.trim()) return;
    await onEdit(msg, editTexto.trim());
    setEditando(false);
  };

  if (msg.eliminado) {
    return (
      <div className={`chat-bubble-row ${isOwn ? "own" : ""}`}>
        {!isOwn && <div className="chat-avatar" style={{ background: avatarColor }}>{getInitials(msg.usuarioNombre)}</div>}
        <div className="chat-bubble-wrap">
          {!isOwn && <span className="chat-sender" style={{ color: avatarColor }}>{msg.usuarioNombre ?? "Usuario"}</span>}
          <div className={`chat-bubble chat-bubble-deleted ${isOwn ? "own" : ""}`}>
            <p className="chat-text-deleted">🚫 Eliminado por el administrador</p>
            <span className="chat-time">{formatHora(msg.fechaEnvio)}</span>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className={`chat-bubble-row ${isOwn ? "own" : ""}`}>
      {!isOwn && (
        <div className="chat-avatar" style={{ background: avatarColor }}>
          {getInitials(msg.usuarioNombre)}
        </div>
      )}
      <div className="chat-bubble-wrap">
        {!isOwn && <span className="chat-sender" style={{ color: avatarColor }}>{msg.usuarioNombre ?? "Usuario"}</span>}

        {msg.respuestaA && (
          <div className="chat-reply-ref">
            <Reply size={12} />
            <span className="chat-reply-ref-name">{msg.respuestaA.usuarioNombre}</span>
            <span className="chat-reply-ref-text">{describeReplyText(msg.respuestaA)}</span>
          </div>
        )}

        <div className={`chat-bubble ${isOwn ? "own" : ""}`}>
          {editando ? (
            <div className="chat-edit-box">
              <textarea
                className="chat-edit-textarea"
                value={editTexto}
                onChange={(e) => setEditTexto(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); void guardarEdicion(); }
                  if (e.key === "Escape") setEditando(false);
                }}
                rows={2}
                autoFocus
              />
              <div className="chat-edit-actions">
                <button type="button" className="chat-action-btn" onClick={() => setEditando(false)} title="Cancelar"><X size={14} /></button>
                <button type="button" className="chat-action-btn" onClick={guardarEdicion} title="Guardar"><Check size={14} /></button>
              </div>
            </div>
          ) : (
            <>
              <Adjunto tipo={msg.archivoTipo} url={msg.archivoUrl} nombre={msg.archivoNombre} />
              {msg.contenido && <p className="chat-text">{msg.contenido}</p>}
              {url && <LinkPreviewCard preview={preview} ytThumb={ytThumb} fallbackUrl={url} />}
              <span className="chat-time">
                {msg.editado && <em className="chat-edited-tag">Editado</em>}
                {formatHora(msg.fechaEnvio)}
              </span>
            </>
          )}

          {msg.reacciones.length > 0 && (
            <div className="chat-reaction-bar">
              {msg.reacciones.map((r) => (
                <button
                  key={r.emoji} type="button"
                  className={`chat-reaction ${r.usuarios.includes(usuarioId) ? "active" : ""}`}
                  onClick={() => onToggleEmoji(msg, r.emoji)} title={r.usuariosNombres.join(", ")}
                >
                  {r.emoji} <span>{r.cantidad}</span>
                </button>
              ))}
            </div>
          )}

          {!editando && (
            <div className="chat-actions" ref={actionsRef}>
              <button type="button" className="chat-action-btn" onClick={() => setShowEmojis((v) => !v)} title="Reaccionar">
                <Smile size={13} />
              </button>
              <button type="button" className="chat-action-btn" onClick={() => onReply(msg)} title="Responder">
                <Reply size={13} />
              </button>
              {isOwn && (
                <button type="button" className="chat-action-btn" onClick={iniciarEdicion} title="Editar">
                  <Pencil size={13} />
                </button>
              )}
              {(isOwn || puedeModerar) && (
                <button type="button" className="chat-action-btn danger" onClick={() => onDelete(msg)} title="Eliminar">
                  <Trash2 size={13} />
                </button>
              )}
              {showEmojis && (
                <div className="chat-emoji-picker">
                  {EMOJIS.map((e) => (
                    <button key={e} type="button" onClick={() => { onToggleEmoji(msg, e); setShowEmojis(false); }}>
                      {e}
                    </button>
                  ))}
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

interface Props {
  canal: Canal | null;
  usuarioId: number;
  esDocente?: boolean;
  onClose: () => void;
}

export function ChatWindow({ canal, usuarioId, esDocente, onClose }: Props) {
  const [temas, setTemas] = useState<Tema[]>([]);
  const [temaActivo, setTemaActivo] = useState<Tema | null>(null);
  const [mensajes, setMensajes] = useState<Mensaje[]>([]);
  const [texto, setTexto] = useState("");
  const [cargando, setCargando] = useState(false);
  const [enviando, setEnviando] = useState(false);
  const [replyMsg, setReplyMsg] = useState<Mensaje | null>(null);
  const [adjPreview, setAdjPreview] = useState<string | null>(null);
  const [adjFile, setAdjFile] = useState<File | null>(null);
  const [nuevoTema, setNuevoTema] = useState("");
  const [creandoTema, setCreandoTema] = useState(false);
  const [escribiendo, setEscribiendo] = useState<string[]>([]);
  const bottomRef = useRef<HTMLDivElement>(null);
  const fileRef = useRef<HTMLInputElement>(null);
  const textareaRef = useRef<HTMLTextAreaElement>(null);
  const lastPingRef = useRef(0);

  useEffect(() => {
    if (!canal) { setTemas([]); setTemaActivo(null); return; }
    fetchTemas(canal.id).then((data) => {
      setTemas(data);
      setTemaActivo(data[0] ?? null);
    }).catch(() => {});
  }, [canal?.id]);

  const cargarMensajes = useCallback(async () => {
    if (!canal) return;
    setCargando(true);
    try {
      const data = await fetchMensajes(canal.id, temaActivo?.id ?? null);
      setMensajes(data);
    } catch { setMensajes([]); }
    finally { setCargando(false); }
  }, [canal?.id, temaActivo?.id]);

  useEffect(() => { void cargarMensajes(); }, [cargarMensajes]);

  useEffect(() => { bottomRef.current?.scrollIntoView({ behavior: "smooth" }); }, [mensajes]);

  useEffect(() => {
    const ta = textareaRef.current;
    if (!ta) return;
    ta.style.height = "auto";
    ta.style.height = Math.min(ta.scrollHeight, 120) + "px";
  }, [texto]);

  // Indicador de "escribiendo": ping al escribir, poll para ver quién más escribe.
  useEffect(() => {
    if (!canal || !texto.trim()) return;
    const ahora = Date.now();
    if (ahora - lastPingRef.current < 2000) return;
    lastPingRef.current = ahora;
    void marcarEscribiendo(canal.id, usuarioId, temaActivo?.id ?? null);
  }, [texto, canal, usuarioId, temaActivo?.id]);

  useEffect(() => {
    if (!canal) { setEscribiendo([]); return; }
    const idCanal = canal.id;
    const temaId = temaActivo?.id ?? null;
    const poll = () => { fetchEscribiendo(idCanal, usuarioId, temaId).then(setEscribiendo).catch(() => {}); };
    poll();
    const interval = setInterval(poll, 2500);
    return () => { clearInterval(interval); setEscribiendo([]); };
  }, [canal?.id, usuarioId, temaActivo?.id]);

  const adoptarArchivo = (file: File) => {
    setAdjFile(file);
    setAdjPreview(file.type.startsWith("image/") || file.type.startsWith("video/") ? URL.createObjectURL(file) : null);
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    adoptarArchivo(file);
  };

  const handlePaste = (e: React.ClipboardEvent<HTMLTextAreaElement>) => {
    const items = e.clipboardData?.items;
    if (!items) return;
    for (const item of items) {
      if (item.kind === "file") {
        const file = item.getAsFile();
        if (file) {
          e.preventDefault();
          adoptarArchivo(file);
          break;
        }
      }
    }
  };

  const clearImg = () => {
    setAdjFile(null); setAdjPreview(null);
    if (fileRef.current) fileRef.current.value = "";
  };

  const handleEnviar = async () => {
    if (!canal || enviando || (!texto.trim() && !adjFile)) return;
    setEnviando(true);
    try {
      let archivoUrl: string | null = null;
      let archivoTipo: TipoArchivoAdjunto | null = null;
      let archivoNombre: string | null = null;
      if (adjFile) {
        const subido = await uploadArchivo(adjFile);
        archivoUrl = subido.url; archivoTipo = subido.tipo; archivoNombre = subido.nombre;
        clearImg();
      }
      const msg = await enviarMensaje(canal.id, usuarioId, texto.trim(), {
        temaId: temaActivo?.id ?? null,
        idMensajeRespuesta: replyMsg?.id ?? null,
        archivoUrl, archivoTipo, archivoNombre,
      });
      setMensajes((prev) => [...prev, msg]);
      setTexto(""); setReplyMsg(null);
    } catch {} finally { setEnviando(false); }
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); void handleEnviar(); }
  };

  const handleDelete = async (msg: Mensaje) => {
    if (!canal || !window.confirm("¿Eliminar este mensaje?")) return;
    try {
      await eliminarMensaje(canal.id, msg.id, usuarioId);
      setMensajes((prev) => prev.map((m) => m.id === msg.id
        ? { ...m, eliminado: true, contenido: null, archivoUrl: null, archivoTipo: null, archivoNombre: null }
        : m));
    } catch {}
  };

  const handleEdit = async (msg: Mensaje, nuevoContenido: string) => {
    if (!canal) return;
    try {
      const actualizado = await editarMensaje(canal.id, msg.id, usuarioId, nuevoContenido);
      setMensajes((prev) => prev.map((m) => m.id === msg.id ? actualizado : m));
    } catch {}
  };

  const handleToggleEmoji = async (msg: Mensaje, emoji: string) => {
    if (!canal) return;
    try {
      const { reacciones } = await toggleReaccion(canal.id, msg.id, usuarioId, emoji);
      setMensajes((prev) => prev.map((m) => m.id === msg.id ? { ...m, reacciones } : m));
    } catch {}
  };

  const handleCrearTema = async () => {
    if (!canal || !nuevoTema.trim()) return;
    setCreandoTema(true);
    try {
      const t = await createTema(canal.id, nuevoTema.trim());
      setTemas((prev) => [...prev, t]);
      setTemaActivo(t); setNuevoTema("");
    } catch {} finally { setCreandoTema(false); }
  };

  const handleDeleteTema = async (t: Tema) => {
    if (!canal || !window.confirm(`¿Eliminar el tema "${t.nombre}"?`)) return;
    try {
      await deleteTema(canal.id, t.id);
      const rest = temas.filter((x) => x.id !== t.id);
      setTemas(rest);
      if (temaActivo?.id === t.id) setTemaActivo(rest[0] ?? null);
    } catch {}
  };

  if (!canal) return null;

  const color = canal.color ?? "#4f46e5";
  const puedeModerar = canal.creadorId === usuarioId;

  const grupos: { fecha: string; items: Mensaje[] }[] = [];
  for (const m of mensajes) {
    const f = formatFecha(m.fechaEnvio);
    if (!grupos.length || grupos[grupos.length - 1].fecha !== f) {
      grupos.push({ fecha: f, items: [m] });
    } else { grupos[grupos.length - 1].items.push(m); }
  }

  return (
    <div className="chat-panel-overlay" onClick={onClose}>
      <div className="chat-panel" onClick={(e) => e.stopPropagation()}>
        <div className="chat-window">
          <div className="chat-header" style={{ borderBottomColor: color }}>
            <div className="chat-header-avatar" style={{ background: color }}>
              {canal.fotoUrl
                ? <img src={canal.fotoUrl} alt={canal.nombre} style={{ width: "100%", height: "100%", objectFit: "cover", borderRadius: "50%" }} />
                : getInitials(canal.nombre)}
            </div>
            <div style={{ flex: 1 }}>
              <h3 className="chat-header-name">{canal.nombre}</h3>
              {canal.descripcion && <p className="chat-header-desc">{canal.descripcion}</p>}
            </div>
            <button type="button" className="chat-action-btn" onClick={onClose} style={{ marginLeft: "auto" }} title="Cerrar">
              <X size={18} />
            </button>
          </div>

          <div className="chat-body">
            <aside className="chat-topics">
              <p className="chat-topics-title">Temas</p>
              {temas.map((t) => (
                <div key={t.id} className={`chat-topic-item ${temaActivo?.id === t.id ? "active" : ""}`}
                  style={temaActivo?.id === t.id ? { borderLeftColor: color } : {}}>
                  <button type="button" className="chat-topic-btn" onClick={() => { setTemaActivo(t); }}>
                    <Hash size={13} /> {t.nombre}
                  </button>
                  {esDocente && (
                    <button type="button" className="chat-topic-del" onClick={() => handleDeleteTema(t)}>
                      <X size={11} />
                    </button>
                  )}
                </div>
              ))}
              {temas.length === 0 && !esDocente && <p className="chat-topics-empty">Sin temas aún</p>}
              {esDocente && (
                <div className="chat-topic-new">
                  <input type="text" placeholder="Nuevo tema…" value={nuevoTema}
                    onChange={(e) => setNuevoTema(e.target.value)}
                    onKeyDown={(e) => { if (e.key === "Enter") void handleCrearTema(); }}
                    maxLength={100} />
                  <button type="button" onClick={handleCrearTema} disabled={creandoTema || !nuevoTema.trim()}>
                    <Send size={12} />
                  </button>
                </div>
              )}
            </aside>

            <div className="chat-messages-area">
              {cargando && <p className="chat-status">Cargando…</p>}
              {!cargando && mensajes.length === 0 && (
                <p className="chat-status">Sin mensajes{temaActivo ? ` en #${temaActivo.nombre}` : ""}. ¡Sé el primero!</p>
              )}
              <div className="chat-messages">
                {grupos.map((g) => (
                  <div key={g.fecha}>
                    <div className="chat-date-sep"><span>{g.fecha}</span></div>
                    {g.items.map((msg) => (
                      <MessageBubble key={msg.id} msg={msg} isOwn={msg.usuarioId === usuarioId}
                        usuarioId={usuarioId} puedeModerar={puedeModerar} onReply={setReplyMsg}
                        onDelete={handleDelete} onToggleEmoji={handleToggleEmoji} onEdit={handleEdit} />
                    ))}
                  </div>
                ))}
                <div ref={bottomRef} />
              </div>

              {escribiendo.length > 0 && (
                <div className="chat-typing-indicator">
                  <span className="chat-typing-dots"><span /><span /><span /></span>
                  {escribiendo.length === 1
                    ? `${escribiendo[0]} está escribiendo…`
                    : `${escribiendo.join(", ")} están escribiendo…`}
                </div>
              )}

              <div className="chat-input-area">
                {replyMsg && (
                  <div className="chat-reply-indicator">
                    <Reply size={13} />
                    <span>Respondiendo a <strong>{replyMsg.usuarioNombre}</strong>:&nbsp;
                      {describeReplyText(replyMsg).slice(0, 60)}
                    </span>
                    <button type="button" onClick={() => setReplyMsg(null)}><X size={13} /></button>
                  </div>
                )}
                {adjFile && (
                  <div className="chat-img-preview">
                    {adjPreview && adjFile.type.startsWith("image/") && <img src={adjPreview} alt="preview" />}
                    {adjPreview && adjFile.type.startsWith("video/") && <video src={adjPreview} className="chat-video-preview" />}
                    {!adjPreview && (
                      <span className="chat-file-preview-chip">
                        <FileIcon nombre={adjFile.name} /> {adjFile.name}
                      </span>
                    )}
                    <button type="button" onClick={clearImg}><X size={13} /></button>
                  </div>
                )}
                <div className="chat-input-row">
                  <button type="button" className="chat-attach-btn" onClick={() => fileRef.current?.click()} title="Adjuntar archivo">
                    <Paperclip size={18} />
                  </button>
                  <input ref={fileRef} type="file" accept={ADJUNTO_ACCEPT} style={{ display: "none" }} onChange={handleFileChange} />
                  <textarea ref={textareaRef} className="chat-textarea"
                    placeholder="Mensaje"
                    value={texto} onChange={(e) => setTexto(e.target.value)}
                    onKeyDown={handleKeyDown} onPaste={handlePaste} rows={1} />
                  <button type="button" className="chat-send-btn" style={{ background: color }}
                    onClick={handleEnviar} disabled={enviando || (!texto.trim() && !adjFile)}>
                    <Send size={16} />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
