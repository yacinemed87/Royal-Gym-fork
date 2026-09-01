// Members Management - UI & Modal Controls

function openAddModal() {
	const modal = document.getElementById("memberModal");
	if (!modal) return;

	document.getElementById("modalTitle").textContent = "Add Member";
	document.getElementById("formAction").value = "add";
	document.getElementById("inputId").value = "";
	document.getElementById("inputName").value = "";
	document.getElementById("inputEmail").value = "";
	document.getElementById("inputPhone").value = "";
	document.getElementById("inputGender").value = "Male";
	
	const planSelect = document.getElementById("inputPlan");
	if (planSelect && planSelect.options.length > 0) {
		planSelect.selectedIndex = 0;
	}

	modal.classList.remove("hidden");
}

function openEditModal(el) {
	let member = el;
	if (el && el.dataset) {
		member = {
			id: el.dataset.id,
			name: el.dataset.name,
			gender: el.dataset.gender,
			email: el.dataset.email,
			phone: el.dataset.phone,
			plan: el.dataset.plan
		};
	} else if (typeof el === "string") {
		try {
			member = JSON.parse(el);
		} catch (e) {
			console.error("Invalid member data", e);
			return;
		}
	}

	const modal = document.getElementById("memberModal");
	if (!modal) {
		console.error("memberModal not found");
		return;
	}

	document.getElementById("modalTitle").textContent = "Edit Member";
	document.getElementById("formAction").value = "edit";
	document.getElementById("inputId").value = member.id || "";
	document.getElementById("inputName").value = member.name || "";
	document.getElementById("inputGender").value = member.gender || "Male";
	document.getElementById("inputEmail").value = member.email || "";
	document.getElementById("inputPhone").value = member.phone || "";

	// Robust plan matching (handles exact, case-insensitive, or partial matches)
	const planSelect = document.getElementById("inputPlan");
	if (planSelect && member.plan) {
		const targetPlan = member.plan.trim().toLowerCase();
		let matched = false;
		for (let opt of planSelect.options) {
			const optVal = opt.value.trim().toLowerCase();
			if (optVal === targetPlan || optVal.includes(targetPlan) || targetPlan.includes(optVal)) {
				planSelect.value = opt.value;
				matched = true;
				break;
			}
		}
		if (!matched && planSelect.options.length > 0) {
			planSelect.selectedIndex = 0;
		}
	}

	modal.classList.remove("hidden");
}

function closeModal() {
	const modal = document.getElementById("memberModal");
	if (modal) modal.classList.add("hidden");
}

// Live client-side instant search & filter
function filterMembers() {
	const search = (document.getElementById("member-search")?.value || "").toLowerCase();
	const filter = (document.getElementById("plan-filter")?.value || "All").toLowerCase();
	const rows = document.querySelectorAll("tbody tr");

	rows.forEach((row) => {
		const name = (row.getAttribute("data-name") || row.cells[0]?.textContent || "").toLowerCase();
		const email = (row.cells[2]?.textContent || "").toLowerCase();
		const plan = (row.getAttribute("data-plan") || row.cells[4]?.textContent || "").trim().toLowerCase();

		const matchesSearch = !search || name.includes(search) || email.includes(search);
		const matchesPlan = filter === "all" || plan === filter || plan.includes(filter) || filter.includes(plan);

		row.style.display = matchesSearch && matchesPlan ? "" : "none";
	});
}

document.getElementById("member-search")?.addEventListener("input", filterMembers);
document.getElementById("plan-filter")?.addEventListener("change", filterMembers);

document.getElementById("memberModal")?.addEventListener("click", (e) => {
	if (e.target === document.getElementById("memberModal")) closeModal();
});

document.addEventListener("keydown", (e) => {
	if (e.key === "Escape") closeModal();
});

// Explicitly bind to window for HTML onclick attributes
window.openAddModal = openAddModal;
window.openEditModal = openEditModal;
window.closeModal = closeModal;
window.filterMembers = filterMembers;
