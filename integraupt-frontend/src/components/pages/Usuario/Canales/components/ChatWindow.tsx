import { useCallback, useEffect, useRef, useState } from "react";
import { Hash, Paperclip, Reply, Send, Smile, Trash2, X } from "lucide-react";
import type { Canal, LinkPreview, Mensaje, Reaccion, Tema } from "../types";
import {
  createTema,
  deleteTema,
  eliminarMensaje,
  enviarMensaje,
  fetchLinkPreview,
  fetchMensajes,
  fetchTemas,
  toggleReaccion,
  uploadImagen,
} from "../services/canalesService";

const EMOJIS = ["❤️", "👍", "😂", "😮", "😢", "👏"];
const URL_RE = /(https?:\/\/[^\s]+)/g;

function detectUrl(text: string): string | null {
  const m = text.match(URL_RE);
  return m ? m[0] : null;
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

function ReactionBar({
  reacciones, usuarioId, onToggle,
}: {
  reacciones: Reaccion[]; usuarioId: number; onToggle: (emoji: string) => void;
}) {
  if (!reacciones.length) return null;
  return (
    <div className="chat-reaction-bar">
      {reacciones.map((r) => (
        <button
          key={r.emoji} type="button"
          className={`chat-reaction ${r.usuarios.includes(usuarioId) ? "active" : ""}`}
          onClick={() => onToggle(r.emoji)} title={`${r.cantidad} reacción(es)`}
        >
          {r.emoji} <span>{r.cantidad}</span>
        </button>
      ))}
    </div>
  );
}

function LinkPreviewCard({ preview }: { preview: LinkPreview }) {
  let host = preview.url;
  try { host = new URL(preview.url).hostname; } catch {}
  return (
    <a href={preview.url} target="_blank" rel="noreferrer" className="chat-link-preview">
      {preview.image && <img src={preview.image} alt="preview" className="chat-link-preview-img" />}
      <div className="chat-link-preview-body">
        <p className="chat-link-preview-site">{preview.siteName ?? host}</p>
        {preview.title && <p className="chat-link-preview-title">{preview.title}</p>}
        {preview.description && <p className="chat-link-preview-desc">{preview.description}</p>}
      </div>
    </a>
  );
}

function MessageBubble({
  msg, isOwn, usuarioId, onReply, onDelete, onToggleEmoji,
}: {
  msg: Mensaje; isOwn: boolean; usuarioId: number;
  onReply: (m: Mensaje) => void;
  onDelete: (m: Mensaje) => void;
  onToggleEmoji: (m: Mensaje, emoji: string) => void;
}) {
  const [showEmojis, setShowEmojis] = useState(false);
  const url = msg.contenido ? detectUrl(msg.contenido) : null;
  const [preview, setPreview] = useState<LinkPreview | null>(null);

  useEffect(() => {
    if (!url) return;
    let cancelled = false;
    fetchLinkPreview(url).then((p) => { if (!cancelled) setPreview(p); }).catch(() => {});
    return () => { cancelled = true; };
  }, [url]);

  return (
    <div className={`chat-bubble-row ${isOwn ? "own" : ""}`}>
      {!isOwn && (
        <div className="chat-avatar">
          {getInitials(msg.usuarioNombre)}
        </div>
      )}
      <div className="chat-bubble-wrap">
        {!isOwn && <span className="chat-sender">{msg.usuarioNombre ?? "Usuario"}</span>}

        {msg.respuestaA && (
          <div className="chat-reply-ref">
            <Reply size={12} />
            <span className="chat-reply-ref-name">{msg.respuestaA.usuarioNombre}</span>
            <span className="chat-reply-ref-text">
              {msg.respuestaA.imagenUrl && !msg.respuestaA.contenido ? "📷 Imagen" : msg.respuestaA.contenido}
            </span>
          </div>
        )}

        <div className={`chat-bubble ${isOwn ? "own" : ""}`}>
          {msg.imagenUrl && (
            <a href={msg.imagenUrl} target="_blank" rel="noreferrer">
              <img src={msg.imagenUrl} alt="imagen enviada" className="chat-img" />
            </a>
          )}
          {msg.contenido && <p className="chat-text">{msg.contenido}</p>}
          {preview && <LinkPreviewCard preview={preview} />}
          <span className="chat-time">{formatHora(msg.fechaEnvio)}</span>
        </div>

        <ReactionBar reacciones={msg.reacciones} usuarioId={usuarioId} onToggle={(e) => onToggleEmoji(msg, e)} />

        <div className="chat-actions">
          <button type="button" className="chat-action-btn" onClick={() => setShowEmojis((v) => !v)} title="Reaccionar">
            <Smile size={13} />
          </button>
          <button type="button" className="chat-action-btn" onClick={() => onReply(msg)} title="Responder">
            <Reply size={13} />
          </button>
          {isOwn && (
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
      </div>
    </div>
  );
}

interface Props {
  canal: Canal | null;
  usuarioId: number;
  esDocente?: boolean;
}

export function ChatWindow({ canal, usuarioId, esDocente }: Props) {
  const [temas, setTemas] = useState<Tema[]>([]);
  const [temaActivo, setTemaActivo] = useState<Tema | null>(null);
  const [mensajes, setMensajes] = useState<Mensaje[]>([]);
  const [texto, setTexto] = useState("");
  const [cargando, setCargando] = useState(false);
  const [enviando, setEnviando] = useState(false);
  const [replyMsg, setReplyMsg] = useState<Mensaje | null>(null);
  const [imgPreview, setImgPreview] = useState<string | null>(null);
  const [imgFile, setImgFile] = useState<File | null>(null);
  const [nuevoTema, setNuevoTema] = useState("");
  const [creandoTema, setCreandoTema] = useState(false);
  const bottomRef = useRef<HTMLDivElement>(null);
  const fileRef = useRef<HTMLInputElement>(null);
  const textareaRef = useRef<HTMLTextAreaElement>(null);

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

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    setImgFile(file);
    setImgPreview(URL.createObjectURL(file));
  };

  const clearImg = () => {
    setImgFile(null); setImgPreview(null);
    if (fileRef.current) fileRef.current.value = "";
  };

  const handleEnviar = async () => {
    if (!canal || enviando || (!texto.trim() && !imgFile)) return;
    setEnviando(true);
    try {
      let imagenUrl: string | null = null;
      if (imgFile) { imagenUrl = await uploadImagen(imgFile); clearImg(); }
      const msg = await enviarMensaje(canal.id, usuarioId, texto.trim(), {
        temaId: temaActivo?.id ?? null,
        idMensajeRespuesta: replyMsg?.id ?? null,
        imagenUrl,
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
      setMensajes((prev) => prev.filter((m) => m.id !== msg.id));
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

  if (!canal) {
    return (
      <div className="chat-empty">
        <Hash size={40} strokeWidth={1.2} />
        <p>Selecciona un canal para chatear</p>
      </div>
    );
  }

  const color = canal.color ?? "#4f46e5";

  const grupos: { fecha: string; items: Mensaje[] }[] = [];
  for (const m of mensajes) {
    const f = formatFecha(m.fechaEnvio);
    if (!grupos.length || grupos[grupos.length - 1].fecha !== f) {
      grupos.push({ fecha: f, items: [m] });
    } else { grupos[grupos.length - 1].items.push(m); }
  }

  return (
    <div className="chat-window">
      <div className="chat-header" style={{ borderBottomColor: color }}>
        <div className="chat-header-avatar" style={{ background: color }}>
          {canal.fotoUrl
            ? <img src={canal.fotoUrl} alt={canal.nombre} style={{ width: "100%", height: "100%", objectFit: "cover", borderRadius: "50%" }} />
            : getInitials(canal.nombre)}
        </div>
        <div>
          <h3 className="chat-header-name">{canal.nombre}</h3>
          {canal.descripcion && <p className="chat-header-desc">{canal.descripcion}</p>}
        </div>
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
                    usuarioId={usuarioId} onReply={setReplyMsg}
                    onDelete={handleDelete} onToggleEmoji={handleToggleEmoji} />
                ))}
              </div>
            ))}
            <div ref={bottomRef} />
          </div>

          <div className="chat-input-area">
            {replyMsg && (
              <div className="chat-reply-indicator">
                <Reply size={13} />
                <span>Respondiendo a <strong>{replyMsg.usuarioNombre}</strong>:&nbsp;
                  {replyMsg.imagenUrl && !replyMsg.contenido ? "📷 Imagen" : replyMsg.contenido?.slice(0, 60)}
                </span>
                <button type="button" onClick={() => setReplyMsg(null)}><X size={13} /></button>
              </div>
            )}
            {imgPreview && (
              <div className="chat-img-preview">
                <img src={imgPreview} alt="preview" />
                <button type="button" onClick={clearImg}><X size={13} /></button>
              </div>
            )}
            <div className="chat-input-row">
              <button type="button" className="chat-attach-btn" onClick={() => fileRef.current?.click()} title="Adjuntar imagen">
                <Paperclip size={18} />
              </button>
              <input ref={fileRef} type="file" accept="image/*" style={{ display: "none" }} onChange={handleFileChange} />
              <textarea ref={textareaRef} className="chat-textarea"
                placeholder={`Mensaje${temaActivo ? ` en #${temaActivo.nombre}` : ""}… (Enter envía, Shift+Enter nueva línea)`}
                value={texto} onChange={(e) => setTexto(e.target.value)}
                onKeyDown={handleKeyDown} rows={1} />
              <button type="button" className="chat-send-btn" style={{ background: color }}
                onClick={handleEnviar} disabled={enviando || (!texto.trim() && !imgFile)}>
                <Send size={16} />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
