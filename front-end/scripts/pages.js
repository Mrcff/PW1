// ========
// HEADER
// ========

//f:pegarHref
function pegarHref() {
  const path = location.pathname;
  return path.includes('/pages/') ? '../' : './';
}
const NAV_LINKS = [
  { label: "Início",        href: "index.html" },
  { label: "Tutorial",      href: "tutorial-page.html" },
  { label: "Jogo",          href: "game-page.html" }
];

//f:pegarNavLink
function pegarNavLink(href) {
  const base = pegarHref();
  const isScreensPage = location.pathname.includes('/pages/');
  const screenPages = ["tutorial-page.html", "game-page.html"];
  if (isScreensPage) {
    if (screenPages.includes(href)) {
      return href;
    }
    return `${base}${href}`;
  }
  if (screenPages.includes(href)) {
    return `pages/${href}`;
  }
  return href;
}

//f:pegarHrefPagina
function pegarHrefPagina(pageName) {
  const isScreensPage = location.pathname.includes('/pages/');
  if (isScreensPage) {
    return pageName;
  }
  return `pages/${pageName}`;
}

//f:injetarHeader
function injetarHeader() {
  const currentPage = location.pathname.split("/").pop() || "index.html";
  const header = document.createElement("header");
  header.className = "site-header";
  header.innerHTML = `
      <a href="${pegarNavLink("index.html")}" class="header-logo">
        <div>
          <div class="header-logo-text">Café do TADS</div>
          <div class="header-logo-sub">Games & Codes</div>
        </div>
      </a>
      <nav class="header-nav">
      ${NAV_LINKS.map(link => {
        const navHref = pegarNavLink(link.href);
        const ativo = link.href === currentPage ? "active" : "";
        return `<a href="${navHref}" class="${ativo}">${link.label}</a>`;
      }).join("")}
    </nav>
    <div class="header-right">
      <span class="header-badge">v0.1.0</span>
    </div>
  `;
  document.body.prepend(header);
}

window.addEventListener("load", () => {
  injetarHeader();
  });
