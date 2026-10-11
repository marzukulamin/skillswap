document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (event) => {
      if (!window.confirm(element.dataset.confirm || 'Are you sure?')) {
        event.preventDefault();
      }
    });
  });

  document.querySelectorAll('[data-rating-input]').forEach((input) => {
    const output = document.querySelector(input.dataset.ratingInput);
    const update = () => {
      if (output) output.textContent = `${input.value}/5`;
    };
    input.addEventListener('input', update);
    update();
  });
});

(() => {
  const key = 'skillswap-demo-tour-v1';
  const plan = [
    { role: 'learner', selector: '[data-tour="booking-form"]', title: 'Choose a session', text: 'Your private learner account is ready. Select a session time, then place the sample booking to send the request.', action: 'book', next: 'mentor' },
    { role: 'mentor', selector: '[data-tour="mentor-action"]', title: 'Accept the incoming request', text: 'The booking has arrived in the mentor inbox. Accept it to update the learner and admin views.', action: 'accept', next: 'mentor' },
    { role: 'mentor', selector: '[data-tour="mentor-action"]', title: 'Complete the session', text: 'Mark the sample session complete. The learner can then leave a review.', action: 'complete', next: 'learner' },
    { role: 'learner', selector: '[data-tour="review-form"]', title: 'Leave a review', text: 'The completed session is visible to the learner. Publish the sample review to send its result to the admin dashboard.', action: 'review', next: 'admin' },
    { role: 'admin', selector: '[data-tour="badge-action"]', title: 'Inspect and award recognition', text: 'The admin can see the booking and review. Award the sample badge to finish the workflow.', action: 'badge', next: 'learner' },
    { role: 'learner', selector: '[data-tour="guide"]', title: 'See the completed result', text: 'The sample booking, review, and badge are all visible here. You have completed the full demo workflow.', action: null, next: null }
  ];
  const role = new URLSearchParams(location.search).get('role') || 'learner';
  const step = plan.findIndex((item) => item.role === role && document.querySelector(item.selector));
  const hasBooking = document.body.textContent.includes('Booking DEMO-001');
  const completedStep = !document.querySelector('[data-tour="booking-form"]') && role === 'learner' && hasBooking ? 5 : step;
  const read = () => { try { return JSON.parse(sessionStorage.getItem(key) || '{}'); } catch { return {}; } };
  const write = (state) => sessionStorage.setItem(key, JSON.stringify(state));
  const state = read();
  const getOverlay = () => document.querySelector('.demo-tour');
  const close = (remember = true) => { getOverlay()?.remove(); document.querySelectorAll('[data-tour-active]').forEach((node) => node.removeAttribute('data-tour-active')); if (remember) write({ dismissed: true }); };
  const show = (index) => {
    const item = plan[index]; const target = item && document.querySelector(item.selector); if (!target) return close(false);
    let overlay = getOverlay();
    if (!overlay) {
      overlay = document.createElement('div'); overlay.className = 'demo-tour';
      overlay.innerHTML = '<div class="demo-tour-shade"></div><div class="demo-tour-pointer" aria-hidden="true">➤</div><section class="demo-tour-card" role="dialog" aria-modal="false" aria-labelledby="demo-tour-title"><div class="demo-tour-count"></div><button class="demo-tour-close" type="button" aria-label="Close guided tour">×</button><h2 id="demo-tour-title" class="h5 mb-2"></h2><p class="demo-tour-copy mb-3"></p><div class="d-flex gap-2"><button class="btn btn-primary btn-sm demo-tour-next" type="button">Show me</button><button class="btn btn-outline-secondary btn-sm demo-tour-skip" type="button">Skip tour</button></div></section>';
      document.body.append(overlay);
      overlay.querySelectorAll('.demo-tour-close,.demo-tour-skip').forEach((button) => button.addEventListener('click', () => close()));
      overlay.querySelector('.demo-tour-next').addEventListener('click', () => {
        const current = plan[Number(overlay.dataset.step)]; if (!current.action) return close();
        const form = document.querySelector(current.selector); const submit = form?.querySelector('button[type="submit"], button:not([type])'); if (!form || !submit) return;
        if (current.action === 'book') form.querySelector('[name="slot"]')?.focus();
        if (current.action === 'review') form.querySelector('[name="rating"]')?.focus();
        submit.scrollIntoView({ behavior: 'smooth', block: 'center' }); submit.classList.add('demo-tour-nudge'); setTimeout(() => submit.classList.remove('demo-tour-nudge'), 1100);
      });
    }
    document.querySelectorAll('[data-tour-active]').forEach((node) => node.removeAttribute('data-tour-active'));
    target.setAttribute('data-tour-active', 'true'); target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    const rect = target.getBoundingClientRect(); const card = overlay.querySelector('.demo-tour-card');
    overlay.querySelector('.demo-tour-count').textContent = `STEP ${index + 1} OF ${plan.length}`;
    overlay.querySelector('#demo-tour-title').textContent = item.title; overlay.querySelector('.demo-tour-copy').textContent = item.text;
    overlay.querySelector('.demo-tour-next').textContent = item.action ? 'Show me' : 'Finish'; overlay.dataset.step = String(index);
    overlay.style.setProperty('--tour-top', `${Math.max(20, Math.min(innerHeight - 30, rect.top + rect.height / 2))}px`);
    overlay.style.setProperty('--tour-left', `${Math.max(20, Math.min(innerWidth - 40, rect.left + Math.min(rect.width / 2, 180)))}px`);
    card.style.top = `${Math.max(16, Math.min(innerHeight - 205, rect.bottom + 18))}px`; card.style.left = `${Math.max(16, Math.min(innerWidth - 346, rect.left))}px`;
  };
  document.querySelector('[data-demo-tour-replay]')?.addEventListener('click', () => { write({ running: true }); show(Math.max(0, step)); });
  document.addEventListener('submit', (event) => { if (event.target.matches('[data-tour="booking-form"],[data-tour="mentor-action"],[data-tour="review-form"],[data-tour="badge-action"]') && read().running) write({ running: true, submitted: true }); });
  if (state.submitted) {
    const message = document.querySelector('.app-alerts')?.textContent.toLowerCase() || '';
    const item = plan.find((candidate) => candidate.role === role && candidate.action && document.querySelector(candidate.selector));
    const success = ['sample booking demo-001', 'session accepted', 'session completed', 'review published', 'badge awarded'].some((phrase) => message.includes(phrase));
    write(success ? { running: true, submitted: false } : { running: true });
    if (success && item?.next) location.replace(`demo.php?role=${item.next}`);
    else if (success) show(5);
  } else if (state.running && !state.dismissed && completedStep >= 0) show(completedStep);
  else if (step === 0 && !state.seen && !state.dismissed) { write({ running: true, seen: true }); show(0); }
  window.addEventListener('resize', () => { const index = Number(getOverlay()?.dataset.step); if (Number.isInteger(index)) show(index); });
})();
