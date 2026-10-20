/* ==========================================================================
   ALEX CARTER — PORTFOLIO · main.js
   Navigation, scroll effects, typing, reveal animations & contact form
   ========================================================================== */

(() => {
  "use strict";

  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* --------------------------------- 1. MOBILE NAV ----------------------- */
  const toggle = document.querySelector(".nav__toggle");
  const menu = document.querySelector(".nav__menu");
  const navLinks = document.querySelectorAll(".nav__link");

  const setMenu = (open) => {
    toggle.setAttribute("aria-expanded", String(open));
    toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    menu.classList.toggle("is-open", open);
    document.body.style.overflow = open ? "hidden" : "";
  };

  toggle.addEventListener("click", () => {
    setMenu(toggle.getAttribute("aria-expanded") !== "true");
  });

  navLinks.forEach((link) =>
    link.addEventListener("click", () => setMenu(false))
  );

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && menu.classList.contains("is-open")) setMenu(false);
  });

  /* --------------------------------- 2. HEADER + PROGRESS BAR ----------- */
  const header = document.querySelector(".site-header");
  const progressBar = document.querySelector(".scroll-progress span");

  const onScroll = () => {
    const y = window.scrollY;
    header.classList.toggle("is-scrolled", y > 20);

    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const percent = docHeight > 0 ? (y / docHeight) * 100 : 0;
    progressBar.style.width = `${percent}%`;
  };

  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* --------------------------------- 3. SCROLL REVEAL ------------------- */
  const revealElements = document.querySelectorAll(".reveal");

  if (prefersReducedMotion || !("IntersectionObserver" in window)) {
    revealElements.forEach((el) => el.classList.add("is-visible"));
  } else {
    const revealObserver = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15, rootMargin: "0px 0px -50px 0px" }
    );

    revealElements.forEach((el) => revealObserver.observe(el));
  }

  /* --------------------------------- 4. ACTIVE NAV LINK ----------------- */
  const sections = document.querySelectorAll("section[id]");

  if ("IntersectionObserver" in window) {
    const sectionObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          const id = entry.target.id;
          navLinks.forEach((link) => {
            const isActive = link.getAttribute("href") === `#${id}`;
            link.classList.toggle("is-active", isActive);
            if (isActive) link.setAttribute("aria-current", "true");
            else link.removeAttribute("aria-current");
          });
        });
      },
      { rootMargin: "-45% 0px -50% 0px" }
    );

    sections.forEach((section) => sectionObserver.observe(section));
  }

  /* --------------------------------- 5. TYPING EFFECT -------------------- */
  const typedTarget = document.querySelector(".typed");
  const roles = [
    "Junior Web Developer.",
    "Responsive UI builder.",
    "HTML · CSS · JavaScript lover.",
    "Future Web Development Technician.",
  ];

  if (typedTarget) {
    if (prefersReducedMotion) {
      typedTarget.textContent = roles[0];
    } else {
      let roleIndex = 0;
      let charIndex = 0;
      let deleting = false;

      const typeSpeed = 75;
      const deleteSpeed = 40;
      const holdDelay = 1600;

      const tick = () => {
        const current = roles[roleIndex];

        typedTarget.textContent = deleting
          ? current.slice(0, charIndex--)
          : current.slice(0, charIndex++);

        let delay = deleting ? deleteSpeed : typeSpeed;

        if (!deleting && charIndex > current.length) {
          deleting = true;
          delay = holdDelay;
        } else if (deleting && charIndex < 0) {
          deleting = false;
          roleIndex = (roleIndex + 1) % roles.length;
          charIndex = 0;
          delay = 350;
        }

        window.setTimeout(tick, delay);
      };

      tick();
    }
  }

  /* --------------------------------- 6. PROJECT CARD GLOW --------------- */
  const cards = document.querySelectorAll(".project-card");

  cards.forEach((card) => {
    card.addEventListener("pointermove", (e) => {
      const rect = card.getBoundingClientRect();
      card.style.setProperty("--mx", `${e.clientX - rect.left}px`);
      card.style.setProperty("--my", `${e.clientY - rect.top}px`);
    });
  });

  /* --------------------------------- 7. CONTACT FORM -------------------- */
  const form = document.getElementById("contact-form");
  const status = document.getElementById("form-status");

  const validators = {
    name: (v) =>
      v.trim().length >= 2 ? "" : "Please enter your name (2 characters minimum).",
    email: (v) =>
      /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim())
        ? ""
        : "Please enter a valid email address.",
    message: (v) =>
      v.trim().length >= 10
        ? ""
        : "Please write a message of at least 10 characters.",
  };

  const validateField = (field) => {
    const error = validators[field.name](field.value);
    const errorEl = document.getElementById(`${field.name}-error`);

    field.classList.toggle("is-invalid", Boolean(error));
    field.setAttribute("aria-invalid", String(Boolean(error)));
    errorEl.textContent = error;

    return !error;
  };

  if (form) {
    form.querySelectorAll("input, textarea").forEach((field) => {
      field.addEventListener("blur", () => validateField(field));
      field.addEventListener("input", () => {
        if (field.classList.contains("is-invalid")) validateField(field);
      });
    });

    form.addEventListener("submit", (e) => {
      e.preventDefault();

      const fields = [...form.querySelectorAll("input, textarea")];
      const allValid = fields.map(validateField).every(Boolean);

      if (!allValid) {
        status.textContent = "Please fix the errors above and try again.";
        status.style.color = "#f87171";
        return;
      }

      // NOTE: this portfolio has no backend. The form opens the visitor's
      // email client with the message ready to send.
      const name = form.elements.name.value.trim();
      const email = form.elements.email.value.trim();
      const message = form.elements.message.value.trim();

      const subject = encodeURIComponent(`Portfolio contact from ${name}`);
      const body = encodeURIComponent(`${message}\n\n— ${name} (${email})`);

      status.style.color = "";
      status.textContent = "Opening your email client… thank you!";
      form.reset();

      window.setTimeout(() => {
        window.location.href = `mailto:alex.carter.dev@example.com?subject=${subject}&body=${body}`;
      }, 600);
    });
  }

  /* --------------------------------- 8. FOOTER YEAR --------------------- */
  const yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();
})();
