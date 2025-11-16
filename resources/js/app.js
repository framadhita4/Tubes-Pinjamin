import './bootstrap';

/* ======================================================
   PinjamIn - script.js (FINAL ULTIMATE)
   ✅ Peminjaman menunggu persetujuan pemilik
   ✅ Peminjam wajib upload foto KTM
   ✅ Pengembalian wajib upload foto
   ✅ Pemilik bisa lihat & perbesar foto (KTM & barang)
   ✅ Pemilik punya daftar barang sendiri + fitur hapus
   ✅ Notifikasi aktif di panel pemilik
   ====================================================== */

   function q(id){ return document.getElementById(id); }
   function save(k,v){ localStorage.setItem(k, JSON.stringify(v)); }
   function load(k){ try{ return JSON.parse(localStorage.getItem(k)); }catch(e){ return null; } }
   
   /* === INITIAL STORAGE === */
   if(!localStorage.getItem("users")) save("users", []);
   if(!localStorage.getItem("items")) save("items", []);
   if(!localStorage.getItem("barangDipinjam")) save("barangDipinjam", []);
   if(!localStorage.getItem("formData")) save("formData", []);
   if(!localStorage.getItem("riwayatPeminjaman")) save("riwayatPeminjaman", []);
   if(!localStorage.getItem("pengembalianPending")) save("pengembalianPending", []);
   
   /* ========== AUTH ========== */
   function register(){
     const nama = q("regNama")?.value?.trim();
     const email = q("regEmail")?.value?.trim()?.toLowerCase();
     const password = q("regPassword")?.value;
     const nim = q("regNIM")?.value?.trim() || "";
     if(!nama || !email || !password) return alert("Isi semua data!");
     const users = load("users") || [];
     if(users.find(u=>u.email===email)){ alert("Email sudah terdaftar."); window.location="login.html"; return; }
     users.push({ nama, email, password, nim });
     save("users", users);
     alert("Registrasi berhasil!");
     window.location="login.html";
   }
   
   function login(){
     const email = q("loginEmail")?.value?.trim()?.toLowerCase();
     const password = q("loginPassword")?.value;
     const users = load("users") || [];
     const user = users.find(u=>u.email===email && u.password===password);
     if(!user){ alert("Email / password salah."); return; }
     localStorage.setItem("isLoggedIn","true");
     localStorage.setItem("loggedUser", JSON.stringify(user));
     window.location = "dashboard.html";
   }
   
   function logout(){
     localStorage.removeItem("isLoggedIn");
     localStorage.removeItem("loggedUser");
     window.location = "index.html";
   }
   
   function getLoggedUser(){ try { return JSON.parse(localStorage.getItem("loggedUser")); } catch(e){ return null; } }
   
   /* ========== UPLOAD ITEM ========== */
   function uploadItem(){
     const name = q("itemName")?.value?.trim();
     const stok = Number(q("itemStok")?.value) || 0;
     const deskripsi = q("itemDeskripsi")?.value?.trim() || "";
     const file = q("itemFoto")?.files[0];
     const maxHari = Number(q("itemMaxHari")?.value) || 7;
     if(!name || stok <= 0 || !file) return alert("Lengkapi nama, stok, dan foto!");
     const user = getLoggedUser();
     if(!user){ alert("Belum login"); window.location="login.html"; return; }
     const reader = new FileReader();
     reader.onload = e => {
       const foto = e.target.result;
       const items = load("items") || [];
       const id = Date.now();
       items.push({ id, nama: name, stok, deskripsi, maxHari, gambar: foto, ownerNama: user.nama, ownerEmail: user.email });
       save("items", items);
       alert("✅ Barang berhasil diupload!");
       window.location = "dashboard.html";
     };
     reader.readAsDataURL(file);
   }
   
   /* ========== DASHBOARD ========== */
   if (document.title.includes("Dashboard")) {
     const user = getLoggedUser();
     if(!user || localStorage.getItem("isLoggedIn") !== "true"){ alert("Kamu harus login!"); window.location="login.html"; }
     q("userWelcome").innerText = `Halo, ${user.nama}!`;
   
     const list = q("barangDashboard");
     const borrowModal = q("modalPeminjaman");
     let currentBorrowItemId = null;
   
     function renderBarang(){
       const items = load("items") || [];
       const dipinjam = load("barangDipinjam") || [];
       const forms = load("formData") || [];
       list.innerHTML = "";
       if(!items.length){ list.innerHTML = "<p class='muted'>Belum ada barang yang diupload.</p>"; return; }
   
       items.forEach(it => {
         const isMine = it.ownerEmail === user.email;
         const stok = it.stok || 0;
         const borrowedEntry = dipinjam.find(d => d.itemId === it.id && d.borrowerEmail === user.email);
         const waiting = forms.find(f => f.idBarang === it.id && f.borrowerEmail === user.email && f.status === "menunggu");
   
         let buttonHtml = "";
         if(isMine){
           buttonHtml = `<button class="btn small danger" onclick="hapusBarang(${it.id})">Hapus Barang</button>`;
         } else if (borrowedEntry) {
           buttonHtml = `<button class="btn" onclick="requestReturn(${it.id})">Kembalikan</button>`;
         } else if (waiting) {
           buttonHtml = `<button class="btn gray" disabled>Menunggu Persetujuan Pemilik</button>`;
         } else if (stok <= 0) {
           buttonHtml = `<button class="btn gray" disabled>Stok Habis</button>`;
         } else {
           buttonHtml = `<button class="btn" onclick="openBorrowModal(${it.id})">Pinjam</button>`;
         }
   
         list.innerHTML += `
           <div class="card">
             <img src="${it.gambar}" alt="${it.nama}">
             <h4>${it.nama}</h4>
             <p class="small">Pemilik: ${it.ownerNama} ${isMine ? '<strong>(Barang Saya)</strong>' : ''}</p>
             <p>Stok: ${stok}</p>
             ${buttonHtml}
           </div>
         `;
       });
     }
   
     window.hapusBarang = function(id){
       const user = getLoggedUser();
       const items = load("items") || [];
       const target = items.find(it => it.id === id);
       if(!target || target.ownerEmail !== user.email) return alert("Kamu bukan pemilik barang ini.");
       if(!confirm("Yakin ingin menghapus barang ini?")) return;
       const newItems = items.filter(it => it.id !== id);
       save("items", newItems);
   
       // hapus juga permintaan peminjaman terkait barang ini
       let forms = load("formData") || [];
       forms = forms.filter(f => f.idBarang !== id);
       save("formData", forms);
   
       alert("🗑️ Barang berhasil dihapus!");
       renderBarang();
     };
   
     renderBarang();
   
     /* === Modal Form Peminjaman === */
     window.openBorrowModal = function(id){
       const items = load("items") || [];
       const item = items.find(x => x.id === id);
       if(!item) return alert("Barang tidak ditemukan.");
       currentBorrowItemId = id;
       q("modalTitle").innerText = `Form Peminjaman — ${item.nama}`;
       borrowModal.style.display = "flex";
       const select = q("fp_lama");
       select.innerHTML = "";
       const maxHari = item.maxHari ? Number(item.maxHari) : 7;
       for(let i=1;i<=maxHari;i++){
         const opt = document.createElement("option");
         opt.value = i;
         opt.textContent = `${i} hari`;
         select.appendChild(opt);
       }
     };
   
     window.closeModal = function(){
       borrowModal.style.display = "none";
       q("formPeminjaman").reset();
     };
   
     q("formPeminjaman")?.addEventListener("submit", function(e){
       e.preventDefault();
       const nama = q("fp_nama").value.trim();
       const nim = q("fp_nim").value.trim();
       const jurusan = q("fp_jurusan").value.trim();
       const alasan = q("fp_alasan").value.trim();
       const lama = Number(q("fp_lama").value) || 1;
       const setuju = q("fp_setuju").checked;
       const ktmFile = q("fp_ktm").files[0];
       if(!nama||!nim||!jurusan||!alasan) return alert("Lengkapi semua data!");
       if(!setuju) return alert("Harus menyetujui pernyataan!");
       if(!ktmFile) return alert("Harus mengunggah foto KTM!");
   
       const reader = new FileReader();
       reader.onload = e2 => {
         const fotoKTM = e2.target.result;
         const items = load("items") || [];
         const forms = load("formData") || [];
         const item = items.find(x => x.id === currentBorrowItemId);
         if(!item) return alert("Barang tidak ditemukan.");
         if(item.ownerEmail === user.email) return alert("Tidak bisa meminjam barang sendiri.");
         if(item.stok <= 0) return alert("Stok habis.");
   
         forms.push({
           idBarang: item.id,
           barang: item.nama,
           nama,
           nim,
           jurusan,
           alasan,
           hari: lama,
           tanggal: new Date().toLocaleString(),
           ownerEmail: item.ownerEmail,
           borrowerEmail: user.email,
           fotoKTM,
           status: "menunggu"
         });
         save("formData", forms);
         alert("✅ Form peminjaman dikirim! Menunggu persetujuan pemilik.");
         renderBarang();
         closeModal();
       };
       reader.readAsDataURL(ktmFile);
     });
   
     /* === Pengembalian: wajib upload foto === */
     let currentReturnItemId = null;
     window.requestReturn = function(itemId){
       const dipinjam = load("barangDipinjam") || [];
       const myBorrow = dipinjam.find(d => d.itemId === itemId && d.borrowerEmail === user.email);
       if(!myBorrow) return alert("Kamu tidak sedang meminjam barang ini.");
       const item = (load("items") || []).find(it => it.id === itemId);
       if(!item) return alert("Barang tidak ditemukan.");
       currentReturnItemId = itemId;
       q("returnItemImage").src = item.gambar;
       q("returnItemName").innerText = item.nama;
       q("modalPengembalian").style.display = "flex";
     };
   
     window.closeReturnModal = function(){
       q("modalPengembalian").style.display = "none";
     };
   
     window.submitReturn = function(){
       const kondisiBaru = q("returnCondition").value.trim();
       const fotoFile = q("returnPhoto").files[0];
       if(!kondisiBaru) return alert("Isi kondisi barang terlebih dahulu.");
       if(!fotoFile) return alert("Harap upload foto kondisi terbaru barang.");
       const reader = new FileReader();
       reader.onload = e => {
         const fotoData = e.target.result;
         const dipinjam = load("barangDipinjam") || [];
         const pending = load("pengembalianPending") || [];
         const myBorrow = dipinjam.find(d => d.itemId === currentReturnItemId && d.borrowerEmail === user.email);
         if(!myBorrow) return alert("Data pinjaman tidak ditemukan.");
         pending.push({
           idBarang: currentReturnItemId,
           barang: myBorrow.barang,
           namaPeminjam: myBorrow.borrowerNama,
           nimPeminjam: myBorrow.borrowerNIM,
           borrowerEmail: myBorrow.borrowerEmail,
           kondisiBaru: kondisiBaru,
           fotoKondisi: fotoData,
           tanggalPengembalian: new Date().toISOString(),
           ownerEmail: myBorrow.ownerEmail
         });
         myBorrow.returnRequested = true;
         save("pengembalianPending", pending);
         save("barangDipinjam", dipinjam);
         alert("✅ Permintaan pengembalian dikirim!");
         q("modalPengembalian").style.display = "none";
         renderBarang();
       };
       reader.readAsDataURL(fotoFile);
     };
   
     window.addEventListener("click", e => {
       if(e.target === borrowModal) closeModal();
       if(e.target === q("modalPengembalian")) closeReturnModal();
     });
   }
   
   /* ========== PANEL PEMILIK ========== */
   if (document.title.includes("Panel Pemilik")) {
     const user = getLoggedUser();
     if(!user){ alert("Harus login!"); window.location="login.html"; }
   
     const notifArea = q("notifArea");
     const tableActive = q("tableActive").getElementsByTagName('tbody')[0];
     const tableBarangSaya = q("tableBarangSaya").getElementsByTagName('tbody')[0];
     const tableHistory = q("tableHistory").getElementsByTagName('tbody')[0];
     const tablePendingReturns = q("tablePendingReturns") ? q("tablePendingReturns").getElementsByTagName('tbody')[0] : null;
   
     function renderPanel(){
       const items = load("items") || [];
       const forms = load("formData") || [];
       const pending = load("pengembalianPending") || [];
       const history = load("riwayatPeminjaman") || [];
   
       const myForms = forms.filter(f => f.ownerEmail === user.email);
       const myPending = pending.filter(p => p.ownerEmail === user.email);
       notifArea.innerText = myForms.length || myPending.length
         ? `🔔 ${myForms.length} permintaan peminjaman, ${myPending.length} pengembalian menunggu konfirmasi`
         : "Tidak ada notifikasi baru.";
   
       // Barang milik saya
       const myItems = items.filter(i => i.ownerEmail === user.email);
       tableBarangSaya.innerHTML = myItems.map((it,i)=>`
         <tr>
           <td>${it.nama}</td>
           <td>${it.stok}</td>
           <td><img src="${it.gambar}" style="max-width:70px;border-radius:6px;cursor:pointer" onclick="openFotoPreview('${it.gambar}')"></td>
           <td><button class="btn small danger" onclick="hapusBarangPemilik(${it.id})">Hapus</button></td>
         </tr>
       `).join("") || `<tr><td colspan="4">Belum ada barang diupload</td></tr>`;
   
       // Permintaan peminjaman
       tableActive.innerHTML = myForms.map((f,i) => `
         <tr>
           <td>${f.nama}</td>
           <td>${f.nim}</td>
           <td>${f.barang}</td>
           <td>${f.hari}</td>
           <td>${f.tanggal}</td>
           <td><img src="${f.fotoKTM}" onclick="openFotoPreview('${f.fotoKTM}')" style="max-width:70px;cursor:pointer;border-radius:6px;"></td>
           <td>${f.status === "menunggu"
             ? `<button class="btn small" onclick="approveBorrow(${i})">Setujui</button> 
                <button class="btn small gray" onclick="rejectBorrow(${i})">Tolak</button>`
             : `<span class='muted'>${f.status}</span>`}</td>
         </tr>
       `).join("") || `<tr><td colspan="7">Tidak ada permintaan peminjaman</td></tr>`;
   
       // Pending pengembalian
       if (tablePendingReturns) {
         tablePendingReturns.innerHTML = myPending.map((p, idx) => `
           <tr>
             <td>${p.namaPeminjam}</td>
             <td>${p.nimPeminjam}</td>
             <td>${p.barang}</td>
             <td>${p.kondisiBaru}</td>
             <td><img src="${p.fotoKondisi}" onclick="openFotoPreview('${p.fotoKondisi}')" style="max-width:80px;cursor:pointer;border-radius:6px;"></td>
             <td>${new Date(p.tanggalPengembalian).toLocaleString()}</td>
             <td><button class="btn small" onclick="approveReturn(${idx})">Setujui</button>
                 <button class="btn small gray" onclick="rejectReturn(${idx})">Tolak</button></td>
           </tr>
         `).join("") || `<tr><td colspan="7">Tidak ada permintaan pengembalian</td></tr>`;
       }
   
       // Riwayat
       const myHistory = history.filter(h => h.ownerEmail === user.email);
       tableHistory.innerHTML = myHistory.map(h => `
         <tr>
           <td>${h.nama}</td>
           <td>${h.nim}</td>
           <td>${h.barang}</td>
           <td>${h.hari}</td>
           <td>${h.tanggal}</td>
           <td>${h.tanggalKembali || '-'}</td>
           <td>${h.kondisiBaru || '-'}</td>
           <td>${h.fotoKondisi ? `<a href="${h.fotoKondisi}" target="_blank">Foto</a>` : '-'}</td>
         </tr>
       `).join("") || `<tr><td colspan="8">Belum ada riwayat</td></tr>`;
     }
   
     /* === Hapus Barang Pemilik === */
     window.hapusBarangPemilik = function(id){
       if(!confirm("Yakin ingin menghapus barang ini?")) return;
       let items = load("items") || [];
       items = items.filter(it => it.id !== id);
       save("items", items);
       alert("🗑️ Barang berhasil dihapus!");
       renderPanel();
     };
   
     /* === Persetujuan Peminjaman === */
     window.approveBorrow = function(index){
       const forms = load("formData") || [];
       const myForms = forms.filter(f => f.ownerEmail === user.email);
       const entry = myForms[index];
       if(!entry) return alert("Data tidak ditemukan.");
       const items = load("items") || [];
       const item = items.find(it => it.id === entry.idBarang);
       if(!item || item.stok <= 0) return alert("Stok barang habis.");
       item.stok -= 1;
       const dipinjam = load("barangDipinjam") || [];
       dipinjam.push({
         itemId: entry.idBarang,
         borrowerNama: entry.nama,
         borrowerNIM: entry.nim,
         borrowerEmail: entry.borrowerEmail,
         tanggal: new Date().toISOString(),
         hari: entry.hari,
         alasan: entry.alasan,
         ownerEmail: entry.ownerEmail,
         returnRequested: false
       });
       const idx = forms.findIndex(f => f.idBarang === entry.idBarang && f.borrowerEmail === entry.borrowerEmail);
       if(idx !== -1) forms[idx].status = "disetujui";
       save("items", items);
       save("barangDipinjam", dipinjam);
       save("formData", forms);
       alert("✅ Permintaan peminjaman disetujui!");
       renderPanel();
     };
   
     window.rejectBorrow = function(index){
       const forms = load("formData") || [];
       const myForms = forms.filter(f => f.ownerEmail === user.email);
       const entry = myForms[index];
       if(!entry) return alert("Data tidak ditemukan.");
       const idx = forms.findIndex(f => f.idBarang === entry.idBarang && f.borrowerEmail === entry.borrowerEmail);
       if(idx !== -1) forms.splice(idx, 1);
       save("formData", forms);
       alert("❌ Permintaan peminjaman ditolak.");
       renderPanel();
     };
   
     /* === Pengembalian === */
     window.approveReturn = function(ownerIndex){
       const pending = load("pengembalianPending") || [];
       const myPending = pending.filter(p => p.ownerEmail === user.email);
       const entry = myPending[ownerIndex];
       if(!entry) return alert("Data pengembalian tidak ditemukan.");
       const realIdx = pending.findIndex(p => p.idBarang === entry.idBarang && p.borrowerEmail === entry.borrowerEmail);
       const items = load("items") || [];
       const item = items.find(it => it.id === entry.idBarang);
       if(item) item.stok = (item.stok || 0) + 1;
       const history = load("riwayatPeminjaman") || [];
       history.push({
         idBarang: entry.idBarang,
         barang: entry.barang,
         nama: entry.namaPeminjam,
         nim: entry.nimPeminjam,
         tanggalKembali: new Date().toLocaleString(),
         kondisiBaru: entry.kondisiBaru,
         fotoKondisi: entry.fotoKondisi,
         ownerEmail: entry.ownerEmail,
         borrowerEmail: entry.borrowerEmail
       });
       const dipinjam = load("barangDipinjam") || [];
       const dipIdx = dipinjam.findIndex(d => d.itemId === entry.idBarang && d.borrowerEmail === entry.borrowerEmail);
       if(dipIdx !== -1) dipinjam.splice(dipIdx, 1);
       pending.splice(realIdx, 1);
       save("items", items);
       save("barangDipinjam", dipinjam);
       save("pengembalianPending", pending);
       save("riwayatPeminjaman", history);
       alert("✅ Pengembalian disetujui.");
       renderPanel();
     };
   
     window.rejectReturn = function(ownerIndex){
       const pending = load("pengembalianPending") || [];
       const myPending = pending.filter(p => p.ownerEmail === user.email);
       const entry = myPending[ownerIndex];
       if(!entry) return alert("Data pengembalian tidak ditemukan.");
       const realIdx = pending.findIndex(p => p.idBarang === entry.idBarang && p.borrowerEmail === entry.borrowerEmail);
       pending.splice(realIdx, 1);
       save("pengembalianPending", pending);
       alert("❌ Pengembalian ditolak.");
       renderPanel();
     };
   
     /* === Modal Foto Preview === */
     window.openFotoPreview = function(src){
       q("previewFoto").src = src;
       q("modalFotoPreview").style.display = "flex";
     };
     window.closeFotoPreview = function(){
       q("modalFotoPreview").style.display = "none";
       q("previewFoto").src = "";
     };
     window.addEventListener("click", e => {
       if (e.target === q("modalFotoPreview")) closeFotoPreview();
     });
   
     renderPanel();
   }
   