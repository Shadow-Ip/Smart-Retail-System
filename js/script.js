// < ---------- alertbox to display messages -------------- >
const alertBox = document.querySelector(".alert-box");

if (alertBox) {
  setTimeout(() => alertBox.classList.add("show"), 50);
  setTimeout(() => {
    alertBox.classList.remove("show");
    setTimeout(() => alertBox.remove(), 1000);
  }, 5000);
}

// < ---------- used to Switch between loginForm and registerForm ------------- >
function switchForm(targetId) {
  const login = document.getElementById("loginForm");
  const register = document.getElementById("registerForm");

  if (targetId === "registerForm") {
    login.classList.remove("active");
    register.classList.add("active");
  } else {
    register.classList.remove("active");
    login.classList.add("active");
  }
}

//< -------- Search Filter -------------- >
const searchInput = document.getElementById("search");
const productCards = document.querySelectorAll(".product-card");
searchInput.addEventListener("input", () => {
  const query = searchInput.value.toLowerCase();
  productCards.forEach((card) => {
    const name = card.getAttribute("data-name");
    card.style.display = name.includes(query) ? "flex" : "none";
  });
});

// < ------------ Sorting Products -------------------- >
const sortSelect = document.getElementById("sort");
const grid = document.getElementById("productGrid");

sortSelect.addEventListener("change", () => {
  const cards = Array.from(grid.children);

  if (sortSelect.value === "price_low") {
    cards.sort((a, b) => a.dataset.price - b.dataset.price);
  } else if (sortSelect.value === "price_high") {
    cards.sort((a, b) => b.dataset.price - a.dataset.price);
  } else if (sortSelect.value === "newest") {
    cards.sort((a, b) => new Date(b.dataset.date) - new Date(a.dataset.date));
  } else if (sortSelect.value === "oldest") {
    cards.sort((a, b) => new Date(a.dataset.date) - new Date(b.dataset.date));
  }

  grid.innerHTML = "";
  cards.forEach((c) => grid.appendChild(c));
});

// < ----------------- auto update Cart ------------------ >
document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll(".add-to-cart-form");
  const cartLink = document.querySelector(".nav-links a[href='Cart.php']");

  forms.forEach((form) => {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(form);
      const res = await fetch("update_cart.php", {
        method: "POST",
        body: formData,
      });

      const data = await res.json();
      if (data.success) {
        // <------- update the cart icon count live --------- >
        cartLink.textContent = `🛒 Cart (${data.cart_count})`;

        // < -------------- show success alert ------------------- >
        showAlert("Successfully added product to cart!", "success");

        // < ------ show a mini success animation on the button ------------- >
        const btn = form.querySelector("button");
        btn.textContent = "Added ✅";
        btn.disabled = true;
        setTimeout(() => {
          btn.textContent = "Add to Cart";
          btn.disabled = false;
        }, 1000);
      }
    });
  });
});

function showAlert(message, type = "success") {
  // <-------- Remove existing alert if any -------- >
  const existing = document.getElementById("alertBox");
  if (existing) existing.remove();

  // <----------- Create new alert ----------- >
  const alertBox = document.createElement("div");
  alertBox.id = "alertBox";
  alertBox.className = "alert-box show";

  alertBox.innerHTML = `
    <div class="alert ${type}">
      <i class='bx bxs-${type === "success" ? "check-circle" : "error"}'></i>
      <span>${message}</span>
    </div>
  `;

  // Add alert to top of body or main
  document.body.appendChild(alertBox);
  // Auto fade-out after 5 seconds
  setTimeout(() => {
    alertBox.classList.add("fade-out");
    setTimeout(() => alertBox.remove(), 1000);
  }, 5000);
}

// < ---------- Update cart count across pages dynamically  --------------- >
async function refreshCartCount() {
  try {
    const res = await fetch("update_cart.php");
    const data = await res.json();
    if (data.success) {
      const cartLink = document.querySelector(".nav-links a[href='Cart.php']");
      if (cartLink) {
        cartLink.textContent = `🛒 Cart (${data.cart_count})`;
      }
    }
  } catch (err) {
    console.error("Cart count refresh failed", err);
  }
}

// < -------------- Alert Box --------------------- >
// Run once on page load and every 5 seconds
document.addEventListener("DOMContentLoaded", refreshCartCount);
setInterval(refreshCartCount, 5000);

document.addEventListener("DOMContentLoaded", () => {
  const alertBox = document.querySelector(".alert-box");
  if (alertBox) {
    // Wait 4.5 seconds then fade out
    setTimeout(() => alertBox.classList.add("fade-out"), 4500);
    setTimeout(() => alertBox.remove(), 5500);
  }
});


