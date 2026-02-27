document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  if (!form) return;

  form.addEventListener('submit', e => {

    // Grab input fields from the form using attribute selectors
    const emailInput = form.querySelector('[name="email"]');
    const passInput = form.querySelector('[name="password"]');

    // Trim whitespace from user inputs
    const email = emailInput.value.trim();
    const pass = passInput.value.trim();

    // Simple but effective: basic client-side validation
    if (!email || !pass) {
      e.preventDefault(); // Stop the form from submitting
      alert('Please enter both e‑mail and password'); // Show error to user
      if (!email) emailInput.focus();
      else passInput.focus();
      return;
    }

    // Simple email format check (you can make it more complex if needed)
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
      e.preventDefault();   // Cancel form submission
      alert('Please enter a valid e-mail address');
      emailInput.focus();
      return;
    }

    // If you want, here you can disable the submit button or show a loader
    // For example: form.querySelector('button[type="submit"]').disabled = true;
  });
});
