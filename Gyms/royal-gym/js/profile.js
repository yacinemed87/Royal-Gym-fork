// Works out how much of the subscription is left, from the dates on #subscription.
// The dates are placeholders in the markup for now — no backend, no database yet.

const MS_PER_DAY = 1000 * 60 * 60 * 24;

function formatDate(date) {
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

// Whole days between two dates, ignoring the time of day.
function daysBetween(from, to) {
    const a = Date.UTC(from.getFullYear(), from.getMonth(), from.getDate());
    const b = Date.UTC(to.getFullYear(), to.getMonth(), to.getDate());
    return Math.round((b - a) / MS_PER_DAY);
}

function renderSubscription() {
    const card = document.getElementById('subscription');
    if (!card) return;

    const start = new Date(card.dataset.start);
    const end = new Date(card.dataset.end);
    const today = new Date();

    const totalDays = daysBetween(start, end);
    const daysLeft = daysBetween(today, end);
    const daysUsed = totalDays - daysLeft;

    document.getElementById('start-date').textContent = formatDate(start);
    document.getElementById('end-date').textContent = formatDate(end);

    const countEl = document.getElementById('days-left');
    const labelEl = document.getElementById('days-label');
    const statusEl = document.getElementById('sub-status');
    const noteEl = document.getElementById('progress-note');
    const barEl = document.getElementById('progress-bar');

    if (daysLeft < 0) {
        countEl.textContent = '0';
        labelEl.textContent = 'days remaining';
        statusEl.textContent = 'Expired';
        card.classList.add('is-expired');
        noteEl.textContent = `Expired ${Math.abs(daysLeft)} day${Math.abs(daysLeft) === 1 ? '' : 's'} ago.`;
        barEl.style.width = '100%';
        return;
    }

    countEl.textContent = daysLeft;
    labelEl.textContent = daysLeft === 1 ? 'day remaining' : 'days remaining';

    if (daysLeft <= 7) {
        statusEl.textContent = 'Expiring soon';
        card.classList.add('is-expiring');
    } else {
        statusEl.textContent = 'Active';
        card.classList.add('is-active');
    }

    noteEl.textContent = `Day ${daysUsed} of ${totalDays}`;
    barEl.style.width = `${Math.min(100, Math.max(0, (daysUsed / totalDays) * 100))}%`;
}

renderSubscription();
