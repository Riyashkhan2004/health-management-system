/* ===========================================
   SMART DOCTOR SYSTEM — APP.JS
   =========================================== */

document.addEventListener("DOMContentLoaded", () => {
  console.log("Smart Doctor System loaded ✅");

  // Fade-in animation for all cards
  const cards = document.querySelectorAll(".card");
  cards.forEach((card, i) => {
    card.style.opacity = 0;
    setTimeout(() => {
      card.style.transition = "all 0.6s ease";
      card.style.opacity = 1;
      card.style.transform = "translateY(0)";
    }, i * 100);
  });

  // Smooth scroll to anchors
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({ behavior: "smooth" });
      }
    });
  });

  // Modal handling
  const openBtns = document.querySelectorAll("[data-modal]");
  const modals = document.querySelectorAll(".modal");
  openBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-modal");
      document.getElementById(id)?.classList.add("active");
    });
  });

  modals.forEach(modal => {
    modal.addEventListener("click", (e) => {
      if (e.target.classList.contains("modal")) {
        modal.classList.remove("active");
      }
    });
  });

  // Navbar shadow on scroll
  window.addEventListener("scroll", () => {
    const nav = document.querySelector("nav");
    if (window.scrollY > 10) {
      nav.style.boxShadow = "0 4px 20px rgba(0,0,0,0.1)";
    } else {
      nav.style.boxShadow = "none";
    }
  });

  // Example toast notification
  window.showToast = (msg, type = "success") => {
    const toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add("show"), 100);
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 500);
    }, 3000);
  };
});

// ===== Toast Notification =====
function showToast(message,type='success'){
    let toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerText = message;
    document.body.appendChild(toast);
    setTimeout(()=> toast.classList.add('show'),100);
    setTimeout(()=> { toast.classList.remove('show'); toast.remove(); },3000);
}


/* ===========================================
   TOAST NOTIFICATION STYLE (Injected)
   =========================================== */
const toastStyle = document.createElement("style");
toastStyle.innerHTML = `
.toast {
  position: fixed;
  bottom: 25px;
  right: 25px;
  padding: 14px 24px;
  border-radius: 12px;
  color: #fff;
  font-weight: 600;
  opacity: 0;
  transform: translateY(20px);
  transition: all .3s ease;
  z-index: 9999;
}
.toast.show { opacity: 1; transform: translateY(0); }
.toast.success { background: var(--accent); }
.toast.error { background: #e74c3c; }
`;
document.head.appendChild(toastStyle);
