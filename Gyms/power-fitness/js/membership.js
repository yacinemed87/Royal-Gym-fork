// ── Interactive Plan Selection & Live Validation ─────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

	// "Choose" button on plan cards auto-selects the radio button & scrolls to form
	document.querySelectorAll('.plan-card .btn-ghost[data-plan]').forEach(btn => {
		btn.addEventListener('click', () => {
			const planName = btn.dataset.plan;
			const radio = document.querySelector(`input[name="plan"][value="${planName}"]`);
			if (radio) {
				radio.checked = true;
				document.querySelector('.register').scrollIntoView({ behavior: 'smooth' });
			}
		});
	});

	// Attach live validators
	const nameInput = document.getElementById('name');
	const emailInput = document.getElementById('email');
	const phoneInput = document.getElementById('phone');
	const genderInput = document.getElementById('gender');

	if (nameInput) nameInput.addEventListener('input', validateName);
	if (emailInput) emailInput.addEventListener('input', validateEmail);
	if (phoneInput) phoneInput.addEventListener('input', validatePhone);
	if (genderInput) genderInput.addEventListener('change', validateGender);

	// Form submit validation
	const form = document.getElementById('register-form');
	if (form) {
		form.addEventListener('submit', function (e) {
			const nameOk = validateName();
			const emailOk = validateEmail();
			const phoneOk = validatePhone();
			const genderOk = validateGender();

			const termsCheckbox = document.querySelector('input[name="terms"]');
			const termsOk = termsCheckbox ? termsCheckbox.checked : true;

			if (!termsOk) {
				alert('Please agree to the terms & conditions before registering.');
				e.preventDefault();
				return;
			}

			// If any validation failed, block submit
			if (!nameOk || !emailOk || !phoneOk || !genderOk) {
				e.preventDefault();
			}
			// Otherwise, let standard PHP form POST execute!
		});
	}
});

// ── Validators ────────────────────────────────────────────────────────────────
function validateName() {
	const input = document.getElementById('name');
	if (!input) return false;
	const name = input.value.trim();
	if (name.length < 3 || !/^[a-zA-Z\s]+$/.test(name)) {
		setError(input, 'name-error', 'Name must be at least 3 letters only');
		return false;
	}
	setSuccess(input, 'name-error');
	return true;
}

function validateEmail() {
	const input = document.getElementById('email');
	if (!input) return false;
	const email = input.value.trim();
	if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
		setError(input, 'email-error', 'Enter a valid email address');
		return false;
	}
	setSuccess(input, 'email-error');
	return true;
}

function validatePhone() {
	const input = document.getElementById('phone');
	if (!input) return false;
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
	if (!input) return false;
	if (!input.value) {
		setError(input, 'gender-error', 'Please select your gender');
		return false;
	}
	setSuccess(input, 'gender-error');
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
