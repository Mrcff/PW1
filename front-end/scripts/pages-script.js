// ========
// HEADER
// ========

const NAV_LINKS = [
  { label: "Início",    href: "index.php"    },
  { label: "Tutorial",  href: "tutorial.php" },
  { label: "Jogo",      href: "game.php"     },
  { label: "Liga",      href: "liga.php"     },
];

const AUTH_LINKS = {
  logado:    { label: "Sair",  href: "/PW1/back-end/login/logout.php" },
  deslogado: { label: "Login", href: "/PW1/back-end/login/login.php"  },
};

//f:pegarBase
function pegarBase() {
  return location.pathname.includes("/pages/") ? "/PW1/front-end/" : "/PW1/front-end/pages/";
}

//f:pegarNavHref
function pegarNavHref(href) {
  if (href.startsWith("/")) return href;
  if (href === "index.php") return "/PW1/front-end/index.php";
  return `/PW1/front-end/pages/${href}`;
}

//f:injetarHeader
function injetarHeader() {
  const currentPage = location.pathname.split("/").pop() || "index.php";

  const usuario = window.usuarioLogado ?? null;

  const authLink = usuario ? AUTH_LINKS.logado : AUTH_LINKS.deslogado;

  const header = document.createElement("header");
  header.className = "site-header";
  header.innerHTML = `
    <a href="/PW1/front-end/index.php" class="header-logo">
      <div>
        <div class="header-logo-text">Café sem Fronteiras</div>
        <div class="header-logo-sub">Games & Codes</div>
      </div>
    </a>
    <nav class="header-nav">
      ${NAV_LINKS.map(({ label, href }) => {
        const navHref = pegarNavHref(href);
        const ativo   = href === currentPage ? "active" : "";
        return `<a href="${navHref}" class="${ativo}">${label}</a>`;
      }).join("")}
    </nav>
    <div class="header-right">
      ${usuario ? `<span class="header-saudacao">Olá, ${usuario.nome}</span>` : ""}
      <a href="${authLink.href}" class="header-auth">${authLink.label}</a>
    </div>
  `;

  document.body.prepend(header);
}

window.addEventListener("DOMContentLoaded", () => {
  injetarHeader();
});
