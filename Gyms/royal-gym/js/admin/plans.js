
let editingId = null;
let deletingId = null;

// ── LocalStorage helpers ──

function getPlans() {
  let data = localStorage.getItem("plans");
  let plans = JSON.parse(data);
  // Guard: if stored in old { male: [], female: [] } format, return empty
  if (plans && !Array.isArray(plans)) return [];
  return plans || [];
}

function savePlans(plans) {
  localStorage.setItem("plans", JSON.stringify(plans));
}

// ── Render ──

function renderPlans() {
  let plans = getPlans();
  let tbody = document.getElementById("plans-tbody");
  tbody.innerHTML = "";

  // Search filter
  let search = document.getElementById("plan-search").value.toLowerCase();

  if (search !== "") {
    plans = plans.filter(p =>
      p.name.toLowerCase().includes(search) ||
      p.features.join(", ").toLowerCase().includes(search)
    );
  }

  tbody.innerHTML = plans.map(plan => `
    <tr>
      <td>${plan.name}</td>
      <td>${Number(plan.price).toLocaleString()} DA</td>
      <td>${plan.duration}</td>
      <td>${plan.features.join(", ")}</td>
      <td>
        <button class="btn btn-edit" onclick="openEditModal(${plan.id})">Edit</button>
        <button class="btn btn-delete" onclick="openDeleteModal(${plan.id})">Delete</button>
      </td>
    </tr>
  `).join("");

  document.getElementById("plan-count").textContent = plans.length;
}

// ── Delete modal ──

function openDeleteModal(id) {
  deletingId = id;
  document.getElementById("deleteModal").classList.remove("hidden");
}

function closeDeleteModal() {
  document.getElementById("deleteModal").classList.add("hidden");
  deletingId = null;
}

function confirmDelete() {
  let plans = getPlans();
  plans = plans.filter(p => p.id !== deletingId);
  savePlans(plans);
  closeDeleteModal();
  renderPlans();
}

// ── Add / Edit modal ──

function openAddModal() {
  editingId = null;
  document.getElementById("modalTitle").textContent = "Add Plan";
  document.getElementById("inputName").value = "";
  document.getElementById("inputPrice").value = "";
  document.getElementById("inputDuration").value = "";
  document.getElementById("inputFeatures").value = "";
  document.getElementById("planModal").classList.remove("hidden");
}

function openEditModal(id) {
  editingId = id;
  let plans = getPlans();
  let plan = plans.find(p => p.id === id);

  document.getElementById("modalTitle").textContent = "Edit Plan";
  document.getElementById("inputName").value = plan.name;
  document.getElementById("inputPrice").value = plan.price;
  document.getElementById("inputDuration").value = plan.duration;
  document.getElementById("inputFeatures").value = plan.features.join(", ");
  document.getElementById("planModal").classList.remove("hidden");
}

function closeModal() {
  document.getElementById("planModal").classList.add("hidden");
  document.getElementById("errorMsg").classList.add("hidden");
}

function savePlan() {
  let name = document.getElementById("inputName").value.trim();
  let price = document.getElementById("inputPrice").value.trim();
  let duration = document.getElementById("inputDuration").value.trim();
  let featuresRaw = document.getElementById("inputFeatures").value.trim();

  // Validation — all fields required
  if (name === "" || price === "" || duration === "" || featuresRaw === "") {
    alert("Please fill in all the fields.");
    return;
  }

  let features = featuresRaw.split(",").map(f => f.trim()).filter(f => f !== "");
  let plans = getPlans();

  // Duplicate name check
  let duplicate = plans.find(p => p.name.toLowerCase() === name.toLowerCase() && p.id !== editingId);
  if (duplicate) {
    document.getElementById("errorMsg").classList.remove("hidden");
    return;
  }

  if (editingId) {
    // Update existing plan
    plans = plans.map(p =>
      p.id === editingId
        ? { ...p, name, price: Number(price), duration, features }
        : p
    );
    editingId = null;
  } else {
    // Create new plan
    let newPlan = {
      id: Date.now(),
      name: name,
      price: Number(price),
      duration: duration,
      features: features
    };
    plans.push(newPlan);
  }

  savePlans(plans);
  closeModal();
  renderPlans();
}

// ── Close modal on overlay click ──
document.getElementById("planModal").addEventListener("click", (e) => {
  if (e.target === document.getElementById("planModal")) {
    closeModal();
  }
});

// ── Close modals on Escape key ──
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    closeModal();
    closeDeleteModal();
  }
});

// ── Expose functions to global window object for HTML onclick handlers ──
window.openAddModal = openAddModal;
window.openEditModal = openEditModal;
window.openDeleteModal = openDeleteModal;
window.closeModal = closeModal;
window.closeDeleteModal = closeDeleteModal;
window.confirmDelete = confirmDelete;
window.savePlan = savePlan;

// ── Search listener ──
document.getElementById("plan-search").addEventListener("input", renderPlans);

// ── Initial render ──
renderPlans();