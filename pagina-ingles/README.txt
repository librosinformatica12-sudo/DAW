PORTFOLIO LANDING PAGE — README
================================

DESCRIPTION
-----------
Modern dark-theme portfolio (landing page) for a final-year Web Development
Technician student, created following the prompt in "pront.txt".
Pure HTML + CSS + JavaScript — no frameworks, no build step.

FOLDER STRUCTURE
----------------
pagina-ingles/
├── index.html          Main page (all sections)
├── css/
│   └── styles.css      Theme, layout, animations, responsive rules
├── js/
│   └── main.js         Navigation, typing effect, scroll animations, form
├── assets/
│   └── favicon.svg     Tab icon (gradient "AC" monogram)
├── guia.txt            Original assignment brief
└── pront.txt           Original prompt used to build the page

SECTIONS
--------
1. Hero          — Name, title, typed roles, intro text, CTA buttons
2. About me      — Passion, professional goals, photo placeholder
3. Skills        — HTML, CSS, JavaScript, responsive design, Git, tools
4. Projects      — 4 project cards (description, tech tags, demo/code links)
5. Services      — 4 freelance services offered
6. Contact       — Email, GitHub, LinkedIn + validated contact form

HOW TO RUN IT LOCALLY
---------------------
Option A (easiest):
  Double-click "index.html" — it opens in your default browser.

Option B (recommended, use a local server):
  1. Install Python 3 (https://python.org) or use any local server.
  2. Open a terminal in this folder and run:
       python -m http.server 8000
  3. Open http://localhost:8000 in your browser.

Note: the fonts (Inter / JetBrains Mono) load from Google Fonts, so an
internet connection gives the best look. Without internet the page still
works using system fonts.

HOW TO CUSTOMIZE (replace the placeholders)
-------------------------------------------
Open "index.html" and search for these placeholder values:

  - "Alex Carter"        -> your real name (also in the footer and favicon)
  - "alexcarter.dev@..." -> your real email
  - "github.com/..."     -> your real GitHub profile URL
  - "linkedin.com/in/..."-> your real LinkedIn profile URL
  - Project cards        -> your real projects, demo and source links (href="#")
  - About photo          -> replace the placehold.co <img> with your photo
    (put your photo in the "assets" folder and update the src attribute)

The contact form has no backend: on submit it validates the fields and then
opens the visitor's email client (mailto). To collect messages without an
email client you would need a form service (Formspree, Netlify Forms, etc.).

ACCESSIBILITY
-------------
- Skip-to-content link, semantic HTML5 landmarks
- ARIA labels, aria-live errors, focus-visible styles
- Respects "prefers-reduced-motion" (disables animations for those users)
- Tested for keyboard navigation and mobile devices

HOW TO PUBLISH (for the assignment link)
-----------------------------------------
Free options to get a public URL:
  1. GitHub Pages  — push this folder to a repo, enable Pages in settings.
  2. Netlify       — drag & drop this folder on app.netlify.com/drop.
  3. Vercel        — import the repo at vercel.com.
All three give you a shareable link in under a minute.
