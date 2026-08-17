const API = '../backend/members.php';

// ── Load and render members from the database ─────────────────────────────────
async function renderMembers() {
  const response = await fetch(API);
  let members = await response.json();

  const search = document.getElementById('member-search').value.toLowerCase();
  const filter = document.getElementById('plan-filter').value;

  if (search !== '') {
    members = members.filter(m =>
      m.name.toLowerCase().includes(search) ||
      m.email.toLowerCase().includes(search)
    );
  }
  if (filter !== 'All') {
    members = members.filter(m => m.plan === filter);
  }

  const table = document.getElementById('members-tbody');
  table.innerHTML = members.map(member => `
        <tr>
            <td>${member.name}</td>
            <td>${member.gender}</td>
            <td>${member.email}</td>
            <td>${member.phone}</td>
            <td><span class="badge badge-${member.plan ? member.plan.toLowerCase() : ''}">${member.plan || '—'}</span></td>
            <td>${member.joinDate}</td>
            <td>
                <button class="btn btn-edit" onclick="openEditModal(${member.id})">Edit</button>
                <button class="btn btn-delete" onclick="openDeleteModal(${member.id})">Delete</button>
            </td>
        </tr>
    `).join('');

  document.getElementById('member-count').textContent = members.length;
}

// ── Modal state ───────────────────────────────────────────────────────────────
let editingId = null;
let deletingId = null;

// ── Add modal ─────────────────────────────────────────────────────────────────
function openAddModal() {
  editingId = null;
  document.getElementById('modalTitle').textContent = 'Add Member';
  document.getElementById('inputName').value = '';
  document.getElementById('inputEmail').value = '';
  document.getElementById('inputPhone').value = '';
  document.getElementById('inputGender').value = 'Male';
  document.getElementById('inputPlan').value = 'Starter';
  document.getElementById('memberModal').classList.remove('hidden');
}

// ── Edit modal — fetch that one member's data ─────────────────────────────────
async function openEditModal(id) {
  editingId = id;
  const response = await fetch(API);
  const members = await response.json();
  const member = members.find(m => m.id == id);

  document.getElementById('modalTitle').textContent = 'Edit Member';
  document.getElementById('inputName').value = member.name;
  document.getElementById('inputGender').value = member.gender || 'Male';
  document.getElementById('inputEmail').value = member.email;
  document.getElementById('inputPlan').value = member.plan || 'Starter';
  document.getElementById('inputPhone').value = member.phone || '';
  document.getElementById('memberModal').classList.remove('hidden');
}

// ── Close modals ──────────────────────────────────────────────────────────────
function closeModal() {
  document.getElementById('memberModal').classList.add('hidden');
  document.getElementById('errorMsg').classList.add('hidden');
}

function openDeleteModal(id) {
  deletingId = id;
  document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
  document.getElementById('deleteModal').classList.add('hidden');
  deletingId = null;
}

// ── Save (add or update) ──────────────────────────────────────────────────────
async function saveMember() {
  const name = document.getElementById('inputName').value.trim();
  const email = document.getElementById('inputEmail').value.trim();
  const plan = document.getElementById('inputPlan').value;
  const gender = document.getElementById('inputGender').value;
  const phone = document.getElementById('inputPhone').value.trim();

  if (!name || !email || !plan || !phone || !gender) {
    alert('Please fill in all the fields.');
    return;
  }

  const payload = { name, gender, email, phone, plan };

  let response;
  if (editingId) {
    // PUT — update existing
    payload.id = editingId;
    response = await fetch(API, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
  } else {
    // POST — add new
    response = await fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
  }

  const result = await response.json();

  if (response.status === 409) {
    document.getElementById('errorMsg').classList.remove('hidden');
    return;
  }

  if (!response.ok) {
    alert('Error: ' + result.error);
    return;
  }

  closeModal();
  renderMembers();
}

// ── Delete ────────────────────────────────────────────────────────────────────
async function confirmDelete() {
  const response = await fetch(API, {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: deletingId })
  });

  if (response.ok) {
    closeDeleteModal();
    renderMembers();
  } else {
    const result = await response.json();
    alert('Error: ' + result.error);
  }
}

// ── Event listeners ───────────────────────────────────────────────────────────
document.getElementById('member-search').addEventListener('input', renderMembers);
document.getElementById('plan-filter').addEventListener('change', renderMembers);

document.getElementById('memberModal').addEventListener('click', (e) => {
  if (e.target === document.getElementById('memberModal')) closeModal();
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') { closeModal(); closeDeleteModal(); }
});

// Expose for HTML onclick attributes
window.openAddModal = openAddModal;
window.openEditModal = openEditModal;
window.openDeleteModal = openDeleteModal;
window.closeModal = closeModal;
window.closeDeleteModal = closeDeleteModal;
window.saveMember = saveMember;
window.confirmDelete = confirmDelete;

// ── Initial load ──────────────────────────────────────────────────────────────
renderMembers();
