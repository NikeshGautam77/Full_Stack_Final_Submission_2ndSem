// ---------------- TABS ----------------

// Highlight active tab
function highlightTab(tabId, colorClass) {
  const allTabs = ['veg-tab', 'nonveg-tab', 'drinks-tab', 'dessert-tab']; // ✅ unified spelling
  allTabs.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active-tab', 'green', 'red', 'blue', 'purple');
  });
  const activeTab = document.getElementById(tabId);
  if (activeTab) activeTab.classList.add('active-tab', colorClass);
}

// Show sections
function showVegItems() {
  hideAllTabs();
  const el = document.getElementById('vegcontents');
  if (el) el.classList.add('active');
  highlightTab('veg-tab', 'red');
}

function showNonVegItems() {
  hideAllTabs();
  const el = document.getElementById('nonvegcontents');
  if (el) el.classList.add('active');
  highlightTab('nonveg-tab', 'green');
}

function showDrinks() { // ✅ renamed to match HTML call
  hideAllTabs();
  const el = document.getElementById('drinkcontents');
  if (el) el.classList.add('active');
  highlightTab('drinks-tab', 'blue');
}

function showDessert() { // ✅ renamed to match HTML call
  hideAllTabs();
  const el = document.getElementById('dessertcontents'); // ✅ unified spelling
  if (el) el.classList.add('active');
  highlightTab('dessert-tab', 'purple');
}

// Hide all tabs
function hideAllTabs() {
  ['vegcontents','nonvegcontents','drinkcontents','dessertcontents'].forEach(id=>{
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
  });
}

// Default load
window.onload = function () {
  showVegItems();
};

// ---------------- SEARCH ----------------
let searchHighlightTimeout;
function searchMenu() {
  const query = document.getElementById("searchInput").value.toLowerCase();
  const allSections = document.querySelectorAll(".tab-content");

  // Clear previous timeout to avoid flicker
  if (searchHighlightTimeout) clearTimeout(searchHighlightTimeout);

  allSections.forEach(section => {
    const cards = section.querySelectorAll(".menu-card");
    cards.forEach(card => {
      const title = card.querySelector("h4").textContent.toLowerCase();
      if (title.includes(query)) {
        card.style.display = "block";
        card.classList.add("highlight");
      } else {
        card.style.display = "none";
        card.classList.remove("highlight");
      }
    });
  });

  // Remove highlight after 5s
  searchHighlightTimeout = setTimeout(() => {
    document.querySelectorAll(".menu-card.highlight").forEach(card => {
      card.classList.remove("highlight");
    });
  }, 5000);
}

// ---------------- FILTER ----------------
function filterMenu() {
  const filterValue = document.getElementById('filter').value;
  const activeSection = document.querySelector('.tab-content.active');
  if (!activeSection) return;

  const menuCards = Array.from(activeSection.querySelectorAll('.menu-card'));
  const menuGrid = activeSection.querySelector('.menu-grid');

  menuCards.sort((a, b) => {
    const priceA = parseFloat(a.querySelector('p').textContent.replace('Rs. ', '')); // ✅ parseFloat
    const priceB = parseFloat(b.querySelector('p').textContent.replace('Rs. ', ''));
    switch(filterValue) {
      case 'price-asc': return priceA - priceB;
      case 'price-desc': return priceB - priceA;
      default: return 0;
    }
  });

  menuGrid.innerHTML = '';
  menuCards.forEach(card => menuGrid.appendChild(card));
}

// ---------------- CART ----------------
let cart = [];

function addToCart(name, price) {
  const existingItem = cart.find(item => item.name === name);
  if (existingItem) {
    existingItem.quantity++;
  } else {
    cart.push({ name, price, quantity: 1 });
  }
  renderCart();
  updateCartCount();
}

function renderCart() {
  const cartList = document.getElementById("cart-items");
  cartList.innerHTML = "";
  cart.forEach(item => {
    const li = document.createElement("li");
    li.innerHTML = `
      <strong>${item.name}</strong> - Rs. ${item.price} x ${item.quantity}
      <button onclick="updateQuantity('${item.name}', -1)">-</button>
      <button onclick="updateQuantity('${item.name}', 1)">+</button>
      <button onclick="removeItem('${item.name}')">Remove</button>
    `;
    cartList.appendChild(li);
  });
  updateTotal();
}

function updateQuantity(name, delta) {
  const item = cart.find(i => i.name === name);
  if (!item) return;
  item.quantity += delta;
  if (item.quantity <= 0) cart = cart.filter(i => i.name !== name);
  renderCart();
  updateCartCount();
}

function removeItem(name) {
  cart = cart.filter(item => item.name !== name);
  renderCart();
  updateCartCount();
}

function updateTotal() {
  const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
  document.getElementById("cart-total").textContent = `Total: Rs. ${total}`;
}

function updateCartCount() {
  const count = cart.reduce((total, item) => total + item.quantity, 0);
  document.getElementById("cart-count").textContent = count;
}

function resetCart() {
  cart = [];
  renderCart();
  updateCartCount();
}

// ---------------- MODALS ----------------
function openCartModal() {
  document.getElementById("cart-modal").classList.add("active");
}
function closeCartModal() {
  document.getElementById("cart-modal").classList.remove("active");
}
function closeOrderModal() {
  document.getElementById('order-modal').style.display = 'none';
}

// ---------------- ORDER FLOW ----------------
function finalOrder() {
  if (cart.length === 0) {
    alert('Your cart is empty!');
    return;
  }
  const summaryList = document.getElementById('order-summary-list');
  const summaryTotal = document.getElementById('order-summary-total');
  summaryList.innerHTML = '';
  let total = 0;
  const cartData = [];

  cart.forEach(item => {
    const li = document.createElement('li');
    li.textContent = `${item.name} - Rs. ${item.price} x ${item.quantity}`;
    summaryList.appendChild(li);
    total += item.price * item.quantity;
    cartData.push({ name: item.name, price: item.price, qty: item.quantity });
  });

  summaryTotal.textContent = `Total: Rs. ${total}`;
  const jsonStr = JSON.stringify(cartData);
  const hidden = document.getElementById('cart-json'); // ✅ must exist in HTML
  if (hidden) hidden.value = jsonStr;

  document.getElementById('order-modal').style.display = 'flex';
}

function submitOrder(event) {
  event.preventDefault();
  if (cart.length === 0) {
    alert('Your cart is empty!');
    return false;
  }
  const cartData = cart.map(item => ({
    name: item.name,
    price: item.price,
    qty: item.quantity
  }));
  const jsonStr = JSON.stringify(cartData);
  const hidden = document.getElementById('cart-json'); // ✅ matches PHP name="cart_json"
  if (hidden) hidden.value = jsonStr;

  // Submit to checkout.php (POST)
  const form = document.getElementById('checkout-form');
  if (form) form.submit();
}