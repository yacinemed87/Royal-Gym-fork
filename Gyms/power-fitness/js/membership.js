// ── Interactive Plan Selection, Duration Switching & Live Validation ──────────
document.addEventListener('DOMContentLoaded', () => {

	const pills = document.querySelectorAll('.duration-pill');
	const durationInput = document.getElementById('duration_id_input');

	// ── Live Order Summary Calculator ──────────────────────────────────────────
	function updateOrderSummary() {
		const checkedDurRadio = document.querySelector('input[name="duration_id"]:checked');
		const activePill = document.querySelector('.duration-pill.active');
		const months = checkedDurRadio ? parseFloat(checkedDurRadio.dataset.months) : (activePill ? parseFloat(activePill.dataset.months) : 1);
		const discount = checkedDurRadio ? (parseFloat(checkedDurRadio.dataset.discount) || 0) : (activePill ? (parseFloat(activePill.dataset.discount) || 0) : 0);
		const durationLabel = months + (months === 1 ? ' Month' : ' Months');

		const checkedRadio = document.querySelector('input[name="plan"]:checked');
		if (!checkedRadio) return;

		const planName = checkedRadio.value;
		const basePrice = parseInt(checkedRadio.dataset.price, 10) || 0;

		const total = Math.round(basePrice * months * (1 - discount / 100));
		const fullPrice = basePrice * months;
		const saved = fullPrice - total;

		// Update Summary Elements
		const planNameEl = document.getElementById('summary-plan-name');
		const durationEl = document.getElementById('summary-duration');
		const badgeEl = document.getElementById('summary-duration-badge');
		const savingsRow = document.getElementById('summary-savings-row');
		const savingsEl = document.getElementById('summary-savings');
		const totalEl = document.getElementById('summary-total-price');
		const pricePaidInput = document.getElementById('price_paid_input');

		if (planNameEl) planNameEl.textContent = planName;
		if (durationEl) durationEl.textContent = durationLabel;
		if (badgeEl) badgeEl.textContent = durationLabel;

		if (savingsRow && savingsEl) {
			if (months > 1 && discount > 0) {
				savingsRow.style.display = 'flex';
				savingsEl.textContent = 'Save ' + saved.toLocaleString('fr-DZ') + ' DA (' + discount + '% off)';
			} else {
				savingsRow.style.display = 'none';
			}
		}

		if (totalEl) totalEl.textContent = total.toLocaleString('fr-DZ') + ' DA';
		if (pricePaidInput) pricePaidInput.value = total;

		// Highlight the selected plan card above
		document.querySelectorAll('.plan-card').forEach(card => {
			const btn = card.querySelector('.btn-ghost[data-plan]');
			if (btn && btn.dataset.plan === planName) {
				card.classList.add('selected-plan');
				btn.textContent = 'Selected ✓';
			} else if (btn) {
				card.classList.remove('selected-plan');
				btn.textContent = 'Choose';
			}
		});
	}

	// ── Set & Synchronize Duration (Pills <-> Form Radios) ──────────────────────
	function setDuration(durId, updateFormRadio = true, updatePill = true) {
		const targetPill = document.querySelector(`.duration-pill[data-duration-id="${durId}"]`);
		const targetRadio = document.querySelector(`input[name="duration_id"][value="${durId}"]`);

		const months = targetRadio ? parseFloat(targetRadio.dataset.months) : (targetPill ? parseFloat(targetPill.dataset.months) : 1);
		const discount = targetRadio ? (parseFloat(targetRadio.dataset.discount) || 0) : (targetPill ? (parseFloat(targetPill.dataset.discount) || 0) : 0);

		if (updatePill && targetPill) {
			pills.forEach(p => p.classList.remove('active'));
			targetPill.classList.add('active');
		}

		if (updateFormRadio && targetRadio) {
			targetRadio.checked = true;
		}

		// Update Plan Card Prices
		document.querySelectorAll('.plan-card').forEach(card => {
			const base = parseInt(card.dataset.basePrice, 10);
			if (isNaN(base)) return;
			const id = card.dataset.planId;
			const total = Math.round(base * months * (1 - discount / 100));
			const perMonth = Math.round(total / months);
			const priceEl = document.getElementById('price-display-' + id);
			const unitEl = document.getElementById('price-unit-' + id);
			const perMonthEl = document.getElementById('price-per-month-' + id);
			const savingsEl = document.getElementById('price-savings-' + id);

			if (priceEl) priceEl.textContent = total.toLocaleString('fr-DZ') + ' DA';
			if (unitEl) unitEl.textContent = '/' + months + (months === 1 ? ' month' : ' months');
			if (perMonthEl) {
				perMonthEl.textContent = months > 1 ? perMonth.toLocaleString('fr-DZ') + ' DA / month' : '';
			}
			if (savingsEl) {
				if (months > 1 && discount > 0) {
					const fullPrice = base * months;
					const saved = fullPrice - total;
					savingsEl.textContent = 'Save ' + saved.toLocaleString('fr-DZ') + ' DA';
				} else {
					savingsEl.textContent = '';
				}
			}
		});

		updateOrderSummary();
	}

	// Duration pills click listener
	pills.forEach(pill => {
		pill.addEventListener('click', () => {
			setDuration(pill.dataset.durationId, true, true);
		});
	});

	// Duration form radio buttons listener
	document.querySelectorAll('input[name="duration_id"]').forEach(radio => {
		radio.addEventListener('change', () => {
			setDuration(radio.value, false, true);
		});
	});

	// Apply default duration on initial load
	const initialCheckedDur = document.querySelector('input[name="duration_id"]:checked') || document.querySelector('.duration-pill.active');
	const initialDurId = initialCheckedDur ? (initialCheckedDur.value || initialCheckedDur.dataset.durationId) : 1;
	setDuration(initialDurId, true, true);

	// ── "Choose" Button on Plan Cards ──────────────────────────────────────────
	document.querySelectorAll('.plan-card .btn-ghost[data-plan]').forEach(btn => {
		btn.addEventListener('click', () => {
			const planName = btn.dataset.plan;
			const radio = document.querySelector(`input[name="plan"][value="${planName}"]`);
			if (radio) {
				radio.checked = true;
				updateOrderSummary();

				// Smooth pop on the total price inside summary
				const totalPriceEl = document.getElementById('summary-total-price');
				if (totalPriceEl) {
					totalPriceEl.classList.remove('price-pop');
					void totalPriceEl.offsetWidth;
					totalPriceEl.classList.add('price-pop');
				}

				// Soft pulse on summary box
				const summaryBox = document.getElementById('order-summary');
				if (summaryBox) {
					summaryBox.classList.remove('pulse-highlight');
					void summaryBox.offsetWidth;
					summaryBox.classList.add('pulse-highlight');
				}

				// Smooth scroll directly to the registration section heading with custom easing
				const regHeading = document.getElementById('register-heading') || document.querySelector('.register');
				if (regHeading) {
					const header = document.querySelector('header');
					const headerHeight = header ? header.getBoundingClientRect().height : 80;
					const targetY = regHeading.getBoundingClientRect().top + window.pageYOffset - headerHeight - 24;

					smoothScrollTo(Math.max(0, targetY), 420);
				}
			}
		});
	});

	// Direct radio button click in form
	document.querySelectorAll('input[name="plan"]').forEach(radio => {
		radio.addEventListener('change', () => {
			updateOrderSummary();
		});
	});

	// ── Form Input Live Validators ─────────────────────────────────────────────
	const nameInput = document.getElementById('name');
	const emailInput = document.getElementById('email');
	const phoneInput = document.getElementById('phone');
	const genderInput = document.getElementById('gender');

	if (nameInput) nameInput.addEventListener('input', validateName);
	if (emailInput) emailInput.addEventListener('input', validateEmail);
	if (phoneInput) phoneInput.addEventListener('input', validatePhone);
	if (genderInput) genderInput.addEventListener('change', validateGender);

	// Form Submit Validation
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

			if (!nameOk || !emailOk || !phoneOk || !genderOk) {
				e.preventDefault();
			}
		});
	}
});

// ── Validator Helpers ──────────────────────────────────────────────────────────
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

// ── Custom Fast & Smooth Scroll with EaseOutCubic ─────────────────────────────
function smoothScrollTo(targetY, duration = 420) {
	const startY = window.pageYOffset;
	const distance = targetY - startY;
	if (Math.abs(distance) < 5) return;

	let startTime = null;
	let cancelled = false;

	const cancel = () => { cancelled = true; };
	window.addEventListener('wheel', cancel, { once: true, passive: true });
	window.addEventListener('touchstart', cancel, { once: true, passive: true });

	// Swift ease-out curve: responds immediately and settles quickly
	function easeOutCubic(t) {
		return 1 - Math.pow(1 - t, 3);
	}

	function step(currentTime) {
		if (cancelled) return;
		if (startTime === null) startTime = currentTime;
		const elapsed = currentTime - startTime;
		const progress = Math.min(elapsed / duration, 1);

		window.scrollTo(0, Math.round(startY + distance * easeOutCubic(progress)));

		if (progress < 1) {
			requestAnimationFrame(step);
		} else {
			window.removeEventListener('wheel', cancel);
			window.removeEventListener('touchstart', cancel);
		}
	}

	requestAnimationFrame(step);
}
