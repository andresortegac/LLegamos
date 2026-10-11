@auth
<button id="push-enable" type="button" hidden style="position:fixed;left:16px;bottom:16px;z-index:9998;border:0;border-radius:999px;padding:12px 18px;background:#22c55e;color:#052e16;font-weight:800;box-shadow:0 10px 30px rgba(0,0,0,.35);cursor:pointer">Activar avisos</button>
@endauth
<button id="pwa-install" type="button" hidden aria-label="Instalar Llegamos" style="position:fixed;right:16px;bottom:16px;z-index:9999;border:0;border-radius:999px;padding:12px 18px;background:#0ea5e9;color:#082f49;font-weight:800;box-shadow:0 10px 30px rgba(0,0,0,.35);cursor:pointer">Instalar app</button>
<div id="pwa-update" hidden style="position:fixed;left:16px;right:16px;bottom:16px;z-index:10000;max-width:560px;margin:auto;padding:14px;background:#1e293b;color:#fff;border:1px solid #38bdf8;border-radius:12px;text-align:center">Hay una nueva version de Llegamos. <button type="button" id="pwa-refresh" style="margin-left:8px;padding:8px 12px;border:0;border-radius:8px;background:#38bdf8;font-weight:700">Actualizar</button></div>
<script>
(() => {
  if (!('serviceWorker' in navigator)) return;
  let installEvent = null;
  const installButton = document.getElementById('pwa-install');
  window.addEventListener('beforeinstallprompt', event => { event.preventDefault(); installEvent = event; installButton.hidden = false; });
  installButton?.addEventListener('click', async () => { if (!installEvent) return; installEvent.prompt(); await installEvent.userChoice; installEvent = null; installButton.hidden = true; });
  window.addEventListener('appinstalled', () => { installButton.hidden = true; });
  navigator.serviceWorker.register('/sw.js', {scope:'/'}).then(registration => {
    const pushButton=document.getElementById('push-enable');
    const b64=value=>{const pad='='.repeat((4-value.length%4)%4);const raw=atob((value+pad).replace(/-/g,'+').replace(/_/g,'/'));return Uint8Array.from([...raw].map(c=>c.charCodeAt(0)));};
    const syncPush=async()=>{if(!pushButton||!('PushManager' in window)||Notification.permission==='denied')return;const existing=await registration.pushManager.getSubscription();pushButton.hidden=!!existing;if(existing)return;pushButton.hidden=false;pushButton.onclick=async()=>{try{const key=await fetch('/push/public-key',{headers:{Accept:'application/json'}}).then(r=>r.json());const sub=await registration.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:b64(key.public_key)});await fetch('/push/subscriptions',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,Accept:'application/json'},body:JSON.stringify({...sub.toJSON(),contentEncoding:(PushManager.supportedContentEncodings||['aes128gcm'])[0]})});pushButton.hidden=true;}catch(e){console.error('No fue posible activar las notificaciones',e);}};};
    syncPush();
    const showUpdate = worker => { document.getElementById('pwa-update').hidden = false; document.getElementById('pwa-refresh').onclick = () => worker.postMessage('SKIP_WAITING'); };
    if (registration.waiting) showUpdate(registration.waiting);
    registration.addEventListener('updatefound', () => { const worker = registration.installing; worker?.addEventListener('statechange', () => { if (worker.state === 'installed' && navigator.serviceWorker.controller) showUpdate(worker); }); });
  }).catch(() => {});
  let refreshing = false;
  navigator.serviceWorker.addEventListener('controllerchange', () => { if (!refreshing) { refreshing = true; location.reload(); } });
})();
</script>
