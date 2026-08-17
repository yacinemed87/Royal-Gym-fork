import { membershipPlans } from './data.js';

// ── Plans source: prefer localStorage (admin edits), fall back to data.js ────
function getPlans() {
	const stored = localStorage.getItem('plans');
	if (stored) {
		try {
			const parsed = JSON.parse(stored);
			if (Array.isArray(parsed) && parsed.length > 0) return parsed;
		} catch (_) { /* ignore corrupt data */ }
	}
	return membershipPlans; // default from data.js
}

// ── Render plan cards ────────────────────────────────────────────────────────
function renderPlanCards() {
	const grid = document.getElementById('plans-grid');
	if (!grid) return;

	const highlightIndex = 1; // Elite is the recommended plan

	const plans = getPlans();
	grid.innerHTML = plans.map((plan, i) => `
		<article class="plan-card${i === highlightIndex ? ' featured' : ''}" aria-labelledby="plan-title-${plan.id}">
			<div class="plan-title">
				<h3 id="plan-title-${plan.id}">${plan.name}</h3>
				<div class="plan-price">
					${Number(plan.price).toLocaleString()} DA<span class="price-unit">/${plan.duration}</span>
				</div>
			</div>

			<ul class="plan-features">
				${plan.features.map(f => `<li>${f}</li>`).join('')}
			</ul>

			<div class="plan-cta">
				${i === highlightIndex ? `<span class="muted">Most popular</span>` : `<span class="muted">&nbsp;</span>`}
				<button class="btn-ghost" data-plan="${plan.name}" aria-label="Choose ${plan.name}">
					Choose
				</button>
			</div>
		</article>
	`).join('');

	// Attach "Choose" button handlers after rendering
	// Re-query after innerHTML update
	grid.querySelectorAll('.btn-ghost[data-plan]').forEach(btn => {
		btn.addEventListener('click', () => {
			const planName = btn.dataset.plan;
			const radio = document.querySelector(`input[name="plan"][value="${planName}"]`);
			if (radio) {
				radio.checked = true;
				document.querySelector('.register').scrollIntoView({ behavior: 'smooth' });
			}
		});
	});
}

// ── Render plan radio buttons ─────────────────────────────────────────────────
function renderPlanRadios() {
	const radioGroup = document.getElementById('plan-radios');
	if (!radioGroup) return;

	const plans = getPlans();
	const defaultIndex = 1; // second plan pre-selected

	radioGroup.innerHTML = plans.map((plan, i) => `
		<label class="custom" title="${plan.name}">
			<input type="radio" name="plan" value="${plan.name}" ${i === 0 ? 'required' : ''} ${i === defaultIndex ? 'checked' : ''} />
			<span class="control radio" aria-hidden="true"></span>
			<span>${plan.name}</span>
		</label>
	`).join('');
}

// ── Live validation listeners ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

	// Render dynamic content first
	renderPlanCards();
	renderPlanRadios();

	// Attach live validators
	document.getElementById('fullname').addEventListener('input', validateName);
	document.getElementById('email').addEventListener('input', validateEmail);
	document.getElementById('phone').addEventListener('input', validatePhone);
	document.getElementById('gender').addEventListener('change', validateGender);
	document.getElementById('dob').addEventListener('change', validateDob);

	// Form submit
	document.getElementById('register-form').addEventListener('submit', function (e) {
		e.preventDefault();

		const nameOk   = validateName();
		const emailOk  = validateEmail();
		const dobOk    = validateDob();
		const phoneOk  = validatePhone();
		const genderOk = validateGender();

		if (nameOk && emailOk && dobOk && phoneOk && genderOk) {
			const isSaved = saveMemberData();
			if (isSaved) {
				document.getElementById('success-msg').style.display = 'block';
				document.getElementById('register-form').reset();
				// Re-render radios so the default is pre-selected again after reset
				renderPlanRadios();
				document.querySelectorAll('.input--success').forEach(el => el.classList.remove('input--success'));
				document.querySelectorAll('.field-msg--success').forEach(el => {
					el.textContent = '';
					el.className = 'field-msg';
				});
			} else {
				document.getElementById('success-msg').style.display = 'none';
			}
		} else {
			document.getElementById('success-msg').style.display = 'none';
		}
	});
});

// ── Validators ────────────────────────────────────────────────────────────────
function validateName() {
	const input = document.getElementById('fullname');
	const name = input.value.trim();
	if (name.length < 3 || !/^[a-zA-Z\s]+$/.test(name)) {
		setError(input, 'fullname-error', 'Name must be at least 3 letters only');
		return false;
	}
	setSuccess(input, 'fullname-error');
	return true;
}

function validateEmail() {
	const input = document.getElementById('email');
	const email = input.value.trim();
	if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
		setError(input, 'email-error', 'Enter a valid email address');
		return false;
	}
	setSuccess(input, 'email-error');
	return true;
}

function validateDob() {
	const input = document.getElementById('dob');
	const dobValue = input.value;
	if (!dobValue) {
		setError(input, 'dob-error', 'Please enter your date of birth');
		return false;
	}
	const age = Math.floor((new Date() - new Date(dobValue)) / (365.25 * 24 * 60 * 60 * 1000));
	if (age < 8) {
		setError(input, 'dob-error', 'You must be at least 8 years old');
		return false;
	}
	setSuccess(input, 'dob-error');
	return true;
}

function validatePhone() {
	const input = document.getElementById('phone');
	const phone = input.value.trim();
	if (!phone) {
		setError(input, 'phone-error', 'Please enter your phone number');
		return false;
	}
	if (!/^0\d{9}$/.test(phone)) {
		setError(input, 'phone-error', 'Enter a valid Algerian number (e.g. 0551234567)');
		return false;
	}
	setSuccess(input, 'phone-error');
	return true;
}

function validateGender() {
	const input = document.getElementById('gender');
	if (!input.value) {
		setError(input, 'gender-error', 'Please select your gender');
		return false;
	}
	setSuccess(input, 'gender-error');
	return true;
}

// ── Save member to localStorage ───────────────────────────────────────────────
function saveMemberData() {
	const name   = document.getElementById('fullname').value.trim();
	const email  = document.getElementById('email').value.trim();
	const phone  = document.getElementById('phone').value.trim();
	const gender = document.getElementById('gender').value;
	const planRadio = document.querySelector('input[name="plan"]:checked');
	const plan = planRadio ? planRadio.value : '';

	let members = JSON.parse(localStorage.getItem('members')) || [];

	// Check for duplicate email
	const duplicate = members.find(m => m.email === email);
	if (duplicate) {
		setError(document.getElementById('email'), 'email-error', 'This email is already registered');
		return false;
	}

	const newMember = {
		id: Date.now(),
		name,
		gender,
		email,
		phone,
		plan,
		joinDate: new Date().toISOString().slice(0, 10)
	};

	members.push(newMember);
	localStorage.setItem('members', JSON.stringify(members));
	return true;
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function setError(input, spanId, message) {
	input.classList.add('input--error');
	input.classList.remove('input--success');
	const span = document.getElementById(spanId);
	if (!span) return;
	span.textContent = '⚠ ' + message;
	span.className = 'field-msg field-msg--error';
}

function setSuccess(input, spanId) {
	input.classList.remove('input--error');
	input.classList.add('input--success');
	const span = document.getElementById(spanId);
	if (!span) return;
	span.textContent = '✓ Looks good';
	span.className = 'field-msg field-msg--success';
}
