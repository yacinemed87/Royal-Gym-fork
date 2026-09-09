// ── Index Membership Plans Duration Switcher & Choose Flow ──────────────────
document.addEventListener('DOMContentLoaded', () => {
	const pills = document.querySelectorAll('.duration-pill');

	function applyDuration(pill) {
		pills.forEach(p => p.classList.remove('active'));
		pill.classList.add('active');

		const months = parseFloat(pill.dataset.months);
		const discount = parseFloat(pill.dataset.discount) || 0;

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

			if (months > 1 && discount > 0) {
				const fullPrice = base * months;
				const saved = fullPrice - total;
				if (perMonthEl) {
					perMonthEl.innerHTML = `<del class="price-strikethrough">${base.toLocaleString('fr-DZ')} DA</del> <span class="rate-number">${perMonth.toLocaleString('fr-DZ')} DA / month</span>`;
				}
				if (savingsEl) {
					savingsEl.textContent = 'Save ' + saved.toLocaleString('fr-DZ') + ' DA';
				}
			} else {
				if (perMonthEl) perMonthEl.innerHTML = '';
				if (savingsEl) savingsEl.textContent = '';
			}
		});
	}

	pills.forEach(pill => {
		pill.addEventListener('click', () => applyDuration(pill));
	});

	// Initialize default 1 Month active state immediately on page load
	const activePill = document.querySelector('.duration-pill.active') || document.querySelector('.duration-pill[data-months="1"]') || pills[0];
	if (activePill) {
		applyDuration(activePill);
	}

	// Choose button: direct to membership.php with chosen plan & duration pre-selected
	document.querySelectorAll('.plan-card .btn-ghost[data-plan]').forEach(btn => {
		btn.addEventListener('click', () => {
			const planName = encodeURIComponent(btn.dataset.plan);
			const activePill = document.querySelector('.duration-pill.active');
			const durId = activePill ? activePill.dataset.durationId : 1;
			window.location.href = `./client/membership.php?plan=${planName}&duration_id=${durId}`;
		});
	});
});
