import{c as r}from"./store.C87EM-IA.js";const n=document.getElementById("filter-buttons"),o=document.getElementById("concept-list");function u(t){const e=document.createElement("div");e.className="bg-white p-6 rounded-lg shadow-sm border border-gray-200";let s="";return t.applyTo==="alumnos"?s=`Alumnos Específicos: ${t.studentName||"N/A"}`:t.applyTo==="semestre"?s=`Semestre: ${t.semester||"N/A"}`:t.applyTo==="carrera"?s=`Carrera: ${t.career||"N/A"}`:s="Todos",e.innerHTML=`
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-lg font-semibold text-gray-800">${t.title}</h3>
        <div class="flex gap-2">
          ${t.status==="Activo"?`
            <button class="px-4 py-2 rounded-lg bg-[#2e594d] text-white font-semibold text-sm finalize-btn" data-id="${t.id}">Finalizar</button>
          `:""}
          <a href="/concepts/${t.id}" class="px-4 py-2 rounded-lg bg-white text-gray-700 border border-gray-300 font-semibold text-sm">
            Editar
          </a>
          <button class="px-4 py-2 rounded-lg bg-red-500 text-white font-semibold text-sm delete-btn" data-id="${t.id}">
            Eliminar
          </button>
        </div>
      </div>
      <p class="text-3xl font-bold text-gray-800 mb-2">${t.amount}</p>
      <p class="text-gray-500 text-sm mb-2">${t.description}</p>
      <div class="mt-4 border-t pt-4 border-gray-200">
        <p class="text-xs text-gray-600">Estado: <span class="font-semibold">${t.status}</span></p>
        <p class="text-xs text-gray-600">Aplicar a: <span class="font-semibold">${s}</span></p>
      </div>
    `,e}function p(t){if(o){if(o.innerHTML="",t.length===0){o.innerHTML='<p class="text-center text-gray-500 mt-10">Aún no hay conceptos creados.</p>';return}t.forEach(e=>{o.appendChild(u(e))})}}function c(t){const e=r.get()||[];let s=e;t==="activos"?s=e.filter(a=>a.status==="Activo"):t==="finalizados"&&(s=e.filter(a=>a.status==="Finalizado")),p(s)}n&&n.addEventListener("click",t=>{const e=t.target.closest("button");if(e){const s=e.dataset.filter;n.querySelectorAll("button").forEach(a=>{a.classList.remove("bg-[#2e594d]","text-white"),a.classList.add("bg-white","text-gray-700","border","border-gray-300")}),e.classList.remove("bg-white","text-gray-700","border","border-gray-300"),e.classList.add("bg-[#2e594d]","text-white"),c(s)}});o&&o.addEventListener("click",t=>{const e=t.target.closest(".finalize-btn"),s=t.target.closest(".delete-btn");if(e){const a=e.dataset.id,d=r.get().map(i=>i.id===a?{...i,status:"Finalizado"}:i);r.set(d)}else if(s&&confirm("¿Estás seguro de que deseas eliminar este concepto?")){const l=s.dataset.id,i=r.get().filter(f=>f.id!==l);r.set(i)}});r.subscribe(()=>{const t=document.querySelector("#filter-buttons .bg-[#2e594d]")?.dataset.filter||"todos";c(t)});
