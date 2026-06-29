import { useCallback, useEffect, useRef, useState } from "react";
import { Download, FileArchive, FileSpreadsheet, FileText, Hash, Paperclip, Reply, Send, Smile, Trash2, X } from "lucide-react";
import type { Canal, LinkPreview, Mensaje, Reaccion, Tema, TipoArchivoAdjunto } from "../types";
import {
  createTema,
  deleteTema,
  eliminarMensaje,
  enviarMensaje,
  fetchLinkPreview,
  fetchMensajes,
  fetchTemas,
  toggleReaccion,
  updateTema,
  uploadArchivo,
} from "../canalesService";

const EMOJIS = ["❤️", "👍", "😂", "😮", "😢", "👏"];
const URL_RE = /(https?:\/\/[^\s]+)/g;
const YOUTUBE_RE = /(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)([\w-]{11})/;
const ADJUNTO_ACCEPT = "image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.7z";

function detectUrl(t: string) { const m = t.match(URL_RE); return m ? m[0] : null; }
function youtubeThumb(url: string): string | null {
  const m = url.match(YOUTUBE_RE);
  return m ? `https://i.ytimg.com/vi/${m[1]}/hqdefault.jpg` : null;
}
function formatHora(iso?: string | null) { if (!iso) return ""; return new Date(iso).toLocaleTimeString("es-PE", { hour: "2-digit", minute: "2-digit" }); }
function formatFecha(iso?: string | null) { if (!iso) return ""; return new Date(iso).toLocaleDateString("es-PE", { day: "numeric", month: "short" }); }
function getInitials(n?: string | null) { if (!n) return "?"; return n.split(" ").slice(0, 2).map((w) => w[0]).join("").toUpperCase(); }
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
    return <a href={url} target="_blank" rel="noreferrer"><img src={url} alt={nombre ?? "imagen enviada"} className="chat-img" /></a>;
  }
  if (tipo === "video") return <video src={url} controls className="chat-video" />;
  if (tipo === "audio") return <audio src={url} controls className="chat-audio" />;
  return (
    <a href={url} target="_blank" rel="noreferrer" className="chat-file-card">
      <FileIcon nombre={nombre} />
      <span className="chat-file-name">{nombre ?? "Archivo"}</span>
      <Download size={14} />
    </a>
  );
}

function ReactionBar({ reacciones, usuarioId, onToggle }: { reacciones: Reaccion[]; usuarioId: number; onToggle: (e: string) => void }) {
  if (!reacciones.length) return null;
  return (
    <div className="chat-reaction-bar">
      {reacciones.map((r) => (
        <button key={r.emoji} type="button"
          className={`chat-reaction ${r.usuarios.includes(usuarioId) ? "active" : ""}`}
          onClick={() => onToggle(r.emoji)}>{r.emoji} <span>{r.cantidad}</span>
        </button>
      ))}
    </div>
  );
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
          <img src={image} alt="" className="chat-link-preview-img" />
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

function MsgBubble({ msg, isOwn, usuarioId, onReply, onDelete, onEmoji }: {
  msg: Mensaje; isOwn: boolean; usuarioId: number;
  onReply: (m: Mensaje) => void; onDelete: (m: Mensaje) => void; onEmoji: (m: Mensaje, e: string) => void;
}) {
  const [showEmojis, setShowEmojis] = useState(false);
  const url = msg.contenido ? detectUrl(msg.contenido) : null;
  const ytThumb = url ? youtubeThumb(url) : null;
  const [preview, setPreview] = useState<LinkPreview | null>(null);
  useEffect(() => {
    setPreview(null);
    if (!url) return;
    let c = false;
    fetchLinkPreview(url).then((p) => { if (!c) setPreview(p); }).catch(() => {});
    return () => { c = true; };
  }, [url]);

  return (
    <div className={`chat-bubble-row ${isOwn ? "own" : ""}`}>
      {!isOwn && <div className="chat-avatar">{getInitials(msg.usuarioNombre)}</div>}
      <div className="chat-bubble-wrap">
        {!isOwn && <span className="chat-sender">{msg.usuarioNombre ?? "Usuario"}</span>}
        {msg.respuestaA && (
          <div className="chat-reply-ref">
            <Reply size={12} />
            <span className="chat-reply-ref-name">{msg.respuestaA.usuarioNombre}</span>
            <span className="chat-reply-ref-text">{msg.respuestaA.archivoUrl && !msg.respuestaA.contenido ? describeAdjunto(msg.respuestaA.archivoTipo) : msg.respuestaA.contenido}</span>
          </div>
        )}
        <div className={`chat-bubble ${isOwn ? "own" : ""}`}>
          <Adjunto tipo={msg.archivoTipo} url={msg.archivoUrl} nombre={msg.archivoNombre} />
          {msg.contenido && <p className="chat-text">{msg.contenido}</p>}
          {url && <LinkPreviewCard preview={preview} ytThumb={ytThumb} fallbackUrl={url} />}
          <span className="chat-time">{formatHora(msg.fechaEnvio)}</span>
        </div>
        <ReactionBar reacciones={msg.reacciones} usuarioId={usuarioId} onToggle={(e) => onEmoji(msg, e)} />
        <div className="chat-actions">
          <button type="button" className="chat-action-btn" onClick={() => setShowEmojis((v) => !v)}><Smile size={13} /></button>
          <button type="button" className="chat-action-btn" onClick={() => onReply(msg)}><Reply size={13} /></button>
          <button type="button" className="chat-action-btn danger" onClick={() => onDelete(msg)}><Trash2 size={13} /></button>
          {showEmojis && (
            <div className="chat-emoji-picker">
              {EMOJIS.map((e) => <button key={e} type="button" onClick={() => { onEmoji(msg, e); setShowEmojis(false); }}>{e}</button>)}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

interface Props { canal: Canal | null; usuarioId: number; esAdmin: boolean; onClose: () => void; }

export function ChatPanel({ canal, usuarioId, esAdmin, onClose }: Props) {
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
  const [editandoTema, setEditandoTema] = useState<Tema | null>(null);
  const [editNombre, setEditNombre] = useState("");
  const bottomRef = useRef<HTMLDivElement>(null);
  const fileRef = useRef<HTMLInputElement>(null);
  const textareaRef = useRef<HTMLTextAreaElement>(null);

  useEffect(() => {
    if (!canal) { setTemas([]); setTemaActivo(null); setMensajes([]); return; }
    fetchTemas(canal.id).then((d) => { setTemas(d); setTemaActivo(d[0] ?? null); }).catch(() => {});
  }, [canal?.id]);

  const cargarMensajes = useCallback(async () => {
    if (!canal) return;
    setCargando(true);
    try { setMensajes(await fetchMensajes(canal.id, temaActivo?.id ?? null)); }
    catch { setMensajes([]); } finally { setCargando(false); }
  }, [canal?.id, temaActivo?.id]);

  useEffect(() => { void cargarMensajes(); }, [cargarMensajes]);
  useEffect(() => { bottomRef.current?.scrollIntoView({ behavior: "smooth" }); }, [mensajes]);
  useEffect(() => {
    const ta = textareaRef.current; if (!ta) return;
    ta.style.height = "auto"; ta.style.height = Math.min(ta.scrollHeight, 120) + "px";
  }, [texto]);

  if (!canal) return null;

  const color = canal.color ?? "#4f46e5";

  const adoptarArchivo = (file: File) => {
    setAdjFile(file);
    setAdjPreview(file.type.startsWith("image/") || file.type.startsWith("video/") ? URL.createObjectURL(file) : null);
  };

  const handlePaste = (e: React.ClipboardEvent<HTMLTextAreaElement>) => {
    const items = e.clipboardData?.items;
    if (!items) return;
    for (const item of items) {
      if (item.kind === "file") {
        const file = item.getAsFile();
        if (file) { e.preventDefault(); adoptarArchivo(file); break; }
      }
    }
  };

  const clearImg = () => { setAdjFile(null); setAdjPreview(null); if (fileRef.current) fileRef.current.value = ""; };

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
        temaId: temaActivo?.id ?? null, idMensajeRespuesta: replyMsg?.id ?? null, archivoUrl, archivoTipo, archivoNombre,
      });
      setMensajes((p) => [...p, msg]); setTexto(""); setReplyMsg(null);
    } catch {} finally { setEnviando(false); }
  };

  const handleDelete = async (msg: Mensaje) => {
    if (!canal || !window.confirm("¿Eliminar?")) return;
    try {
      await eliminarMensaje(canal.id, msg.id, usuarioId, esAdmin);
      setMensajes((p) => p.filter((m) => m.id !== msg.id));
    } catch {}
  };

  const handleEmoji = async (msg: Mensaje, emoji: string) => {
    if (!canal) return;
    try {
      const { reacciones } = await toggleReaccion(canal.id, msg.id, usuarioId, emoji);
      setMensajes((p) => p.map((m) => m.id === msg.id ? { ...m, reacciones } : m));
    } catch {}
  };

  const handleCrearTema = async () => {
    if (!canal || !nuevoTema.trim()) return;
    try {
      const t = await createTema(canal.id, nuevoTema.trim());
      setTemas((p) => [...p, t]); setTemaActivo(t); setNuevoTema("");
    } catch {}
  };

  const handleDeleteTema = async (t: Tema) => {
    if (!canal || !window.confirm(`¿Eliminar tema "${t.nombre}"?`)) return;
    try {
      await deleteTema(canal.id, t.id);
      const rest = temas.filter((x) => x.id !== t.id);
      setTemas(rest); if (temaActivo?.id === t.id) setTemaActivo(rest[0] ?? null);
    } catch {}
  };

  const handleSaveEditTema = async () => {
    if (!canal || !editandoTema || !editNombre.trim()) return;
    try {
      const t = await updateTema(canal.id, editandoTema.id, editNombre.trim());
      setTemas((p) => p.map((x) => x.id === t.id ? t : x));
      if (temaActivo?.id === t.id) setTemaActivo(t);
      setEditandoTema(null);
    } catch {}
  };

  const grupos: { fecha: string; items: Mensaje[] }[] = [];
  for (const m of mensajes) {
    const f = formatFecha(m.fechaEnvio);
    if (!grupos.length || grupos[grupos.length - 1].fecha !== f) grupos.push({ fecha: f, items: [m] });
    else grupos[grupos.length - 1].items.push(m);
  }

  return (
    <div className="chat-panel-overlay">
      <div className="chat-panel">
        <div className="chat-header" style={{ borderBottomColor: color }}>
          <div className="chat-header-avatar" style={{ background: color }}>
            {canal.fotoUrl
              ? <img src={canal.fotoUrl} alt="" style={{ width: "100%", height: "100%", objectFit: "cover", borderRadius: "50%" }} />
              : getInitials(canal.nombre)}
          </div>
          <div style={{ flex: 1 }}>
            <h3 className="chat-header-name">{canal.nombre}</h3>
            {canal.descripcion && <p className="chat-header-desc">{canal.descripcion}</p>}
          </div>
          <button type="button" className="chat-action-btn" onClick={onClose} style={{ marginLeft: "auto" }}><X size={18} /></button>
        </div>

        <div className="chat-body">
          <aside className="chat-topics">
            <p className="chat-topics-title">Temas</p>
            {temas.map((t) => (
              <div key={t.id} className={`chat-topic-item ${temaActivo?.id === t.id ? "active" : ""}`}
                style={temaActivo?.id === t.id ? { borderLeftColor: color } : {}}>
                {editandoTema?.id === t.id
                  ? <div className="chat-topic-edit">
                      <input value={editNombre} onChange={(e) => setEditNombre(e.target.value)}
                        onKeyDown={(e) => { if (e.key === "Enter") void handleSaveEditTema(); if (e.key === "Escape") setEditandoTema(null); }}
                        autoFocus />
                      <button type="button" onClick={handleSaveEditTema}><Send size={11} /></button>
                      <button type="button" onClick={() => setEditandoTema(null)}><X size={11} /></button>
                    </div>
                  : <>
                      <button type="button" className="chat-topic-btn" onClick={() => setTemaActivo(t)}>
                        <Hash size={13} /> {t.nombre}
                      </button>
                      <button type="button" className="chat-topic-del"
                        onClick={() => { setEditandoTema(t); setEditNombre(t.nombre); }} title="Renombrar">✏️</button>
                      <button type="button" className="chat-topic-del" onClick={() => handleDeleteTema(t)}><X size={11} /></button>
                    </>}
              </div>
            ))}
            <div className="chat-topic-new">
              <input type="text" placeholder="Nuevo tema…" value={nuevoTema}
                onChange={(e) => setNuevoTema(e.target.value)}
                onKeyDown={(e) => { if (e.key === "Enter") void handleCrearTema(); }} maxLength={100} />
              <button type="button" onClick={handleCrearTema} disabled={!nuevoTema.trim()}><Send size={12} /></button>
            </div>
          </aside>

          <div className="chat-messages-area">
            {cargando && <p className="chat-status">Cargando…</p>}
            {!cargando && !mensajes.length && <p className="chat-status">Sin mensajes{temaActivo ? ` en #${temaActivo.nombre}` : ""}.</p>}
            <div className="chat-messages">
              {grupos.map((g) => (
                <div key={g.fecha}>
                  <div className="chat-date-sep"><span>{g.fecha}</span></div>
                  {g.items.map((msg) => (
                    <MsgBubble key={msg.id} msg={msg} isOwn={msg.usuarioId === usuarioId}
                      usuarioId={usuarioId} onReply={setReplyMsg} onDelete={handleDelete} onEmoji={handleEmoji} />
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
                    {replyMsg.archivoUrl && !replyMsg.contenido ? describeAdjunto(replyMsg.archivoTipo) : replyMsg.contenido?.slice(0, 60)}
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
                <button type="button" className="chat-attach-btn" onClick={() => fileRef.current?.click()}><Paperclip size={18} /></button>
                <input ref={fileRef} type="file" accept={ADJUNTO_ACCEPT} style={{ display: "none" }}
                  onChange={(e) => { const f = e.target.files?.[0]; if (f) adoptarArchivo(f); }} />
                <textarea ref={textareaRef} className="chat-textarea"
                  placeholder={`Mensaje${temaActivo ? ` en #${temaActivo.nombre}` : ""}… (Enter envía, Ctrl+V pega imágenes)`}
                  value={texto} onChange={(e) => setTexto(e.target.value)}
                  onKeyDown={(e) => { if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); void handleEnviar(); } }}
                  onPaste={handlePaste}
                  rows={1} />
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
  );
}
