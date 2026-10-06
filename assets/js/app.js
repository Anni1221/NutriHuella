document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('[data-confirm]').forEach(b=>b.addEventListener('click',e=>{if(!confirm(b.dataset.confirm))e.preventDefault()}));});
function calcularPorcion(){const peso=parseFloat(document.getElementById('peso')?.value||0), factor=parseFloat(document.getElementById('factor')?.value||0);const out=document.getElementById('resultado');if(out)out.textContent=peso&&factor?(peso*factor).toFixed(0)+' g/día':'—';}

// Mostrar/ocultar contraseña (botones con data-ver-clave="id_del_input")
document.addEventListener('click', e => {
  const b = e.target.closest('[data-ver-clave]'); if (!b) return;
  const inp = document.getElementById(b.dataset.verClave); if (!inp) return;
  const ver = inp.type === 'password';
  inp.type = ver ? 'text' : 'password';
  b.textContent = ver ? 'Ocultar' : 'Mostrar';
  b.setAttribute('aria-label', ver ? 'Ocultar contraseña' : 'Mostrar contraseña');
});
// Validación en vivo del registro (el servidor valida de nuevo)
document.addEventListener('DOMContentLoaded', () => {
  const f = document.getElementById('form-registro'); if (!f) return;
  const nom = f.nombre, cor = f.correo, c1 = f.clave, c2 = f.clave2;
  const reNombre = /^(?=(?:[^\p{L}]*\p{L}){2})\p{L}[\p{L}'’.-]*(?: (?=(?:[^\p{L}]*\p{L}){2})\p{L}[\p{L}'’.-]*)+$/u;
  const reCorreo = /^[^\s@]+@([a-z0-9-]+\.)+[a-z]{2,}$/i;
  const marca = (el, ok) => { el.classList.toggle('is-invalid', !ok); el.classList.toggle('is-valid', ok); return ok; };
  const vNombre = () => marca(nom, reNombre.test(nom.value.trim().replace(/\s+/g, ' ')));
  const vCorreo = () => marca(cor, reCorreo.test(cor.value.trim()));
  const vClave  = () => marca(c1, c1.value.length >= 8 && c1.value.length <= 72);
  const vClave2 = () => marca(c2, c2.value !== '' && c2.value === c1.value);
  nom.addEventListener('blur', vNombre); cor.addEventListener('blur', vCorreo);
  c1.addEventListener('blur', vClave);   c2.addEventListener('blur', vClave2);
  [[nom, vNombre], [cor, vCorreo], [c1, vClave], [c2, vClave2]].forEach(([el, fn]) =>
    el.addEventListener('input', () => { if (el.classList.contains('is-invalid') || el.classList.contains('is-valid')) fn(); }));
  c1.addEventListener('input', () => { if (c2.value) vClave2(); });
  f.addEventListener('submit', e => {
    const ok = [vNombre(), vCorreo(), vClave(), vClave2()].every(Boolean);
    if (!ok) { e.preventDefault(); f.querySelector('.is-invalid')?.focus(); }
  });
});
