document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.getElementById("modoOscuro");

  const dark = localStorage.getItem("modo") === "oscuro";
  if (dark) {
    document.body.classList.add("dark-mode");
    toggle.checked = true;
  }

  toggle.addEventListener("change", () => {
    if (toggle.checked) {
      document.body.classList.add("dark-mode");
      localStorage.setItem("modo", "oscuro");
    } else {
      document.body.classList.remove("dark-mode");
      localStorage.setItem("modo", "claro");
    }
  });
});
