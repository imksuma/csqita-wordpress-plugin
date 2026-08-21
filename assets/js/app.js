(function () {
  "use strict";

  var TOKEN_KEY = "csqita_login_token";

  var rootEl = document.getElementById("csqita-wp-plugin-root");
  var dashboardEl = document.querySelector(".csqita-dashboard");

  // -----------------------------------------------------------------
  // Dummy network layer — replace these with real endpoints later.
  // -----------------------------------------------------------------

  function LoginRequest(username, password) {
    return new Promise(async function (resolve, reject) {
      if (!username || !password) {
        reject(new Error("Username and password are required."));
        return;
      }
      const res = await fetch(window.csqita.remoteEndpoint + "/anon/v1/login", {
        method: "POST",
        body: JSON.stringify({
          username: username,
          password: password,
          site: window.csqita.siteUrl,
        }),
        headers: {
          // Authorization: 'Bearer ' + Cookies.get('tokenAnon'),
          "Content-Type": "application/json",
        },
      });
      if (res.status === 200) {
        const data = await res.json();
        resolve({
          token: data.data.token,
          name: data.data.name,
          userid: data.data.userid,
        });
        return;
      }
      reject(new Error("Authentication failed"));
    });
  }

  function ActivateCardRequest(cardId) {
    return new Promise(async function (resolve, reject) {
      const res = await fetch(
        window.csqita.remoteEndpoint + "/user/v1/chatbot",
        {
          method: "POST",
          body: JSON.stringify({ id: cardId, domain: window.csqita.siteUrl }),
          headers: {
            Authorization: "Bearer " + getToken(),
            "Content-Type": "application/json",
          },
        },
      );
      if (res.status === 200) {
        const res = await fetch(
          window.csqita.remoteEndpoint + "/user/v1/integration/token",
          {
            method: "POST",
            body: JSON.stringify({ id: cardId }),
            headers: {
              Authorization: "Bearer " + getToken(),
              "Content-Type": "application/json",
            },
          },
        );
        if (res.status === 200) {
          const data = await res.json();
          wp.apiFetch({
            path: `${window.csqita.internalEndpoint}/user/save`,
            method: "POST",
            data: {
              nonce: window.csqita.nonce,
              widgetToken: data?.data?.token,
            },
          }).then((newPost) => {
            console.log("Post user save done with status", newPost.code);
          });
          return;
        }
      }
      reject(new Error("Authentication failed"));
    });
  }

  function DeactivateCardRequest(cardId) {
    return new Promise(async function (resolve, reject) {
      const res = await fetch(
        window.csqita.remoteEndpoint + "/user/v1/chatbot",
        {
          method: "POST",
          body: JSON.stringify({ id: cardId, domain: "" }),
          headers: {
            Authorization: "Bearer " + getToken(),
            "Content-Type": "application/json",
          },
        },
      );
      if (res.status === 200) {
        const data = await res.json();
        resolve({
          token: data.token,
          name: data.name,
          userid: data.userid,
        });
        return;
      }
      reject(new Error("Authentication failed"));
    });
  }

  function GetCardsRequest() {
    return new Promise(async function (resolve, reject) {
      let chatbotsD = [];
      const res = await fetch(
        window.csqita.remoteEndpoint + "/user/v1/chatbot",
        {
          method: "GET",
          headers: {
            Authorization: "Bearer " + getToken(),
            "Content-Type": "application/json",
          },
        },
      );
      if (res.status === 200) {
        const data = await res.json();
        const chatbots = data?.data?.chatbots;
        const check = { chatbot: null, isActive: false };
        for (let index = 0; index < chatbots.length; index++) {
          const element = chatbots[index];
          const res = await fetch(
            window.csqita.remoteEndpoint + `/user/v1/domain?id=${element.id}`,
            {
              method: "GET",
              headers: {
                Authorization: "Bearer " + getToken(),
                "Content-Type": "application/json",
              },
            },
          );
          if (res.status === 200) {
            const data = await res.json();
            element["domain"] = data?.data;
            element["status"] =
              element["domain"] === window.csqita.siteUrl
                ? "active"
                : "deactive";
            check.isActive =
              check.isActive || element["domain"] === window.csqita.siteUrl;
            element["available"] =
              element["domain"] === "" ||
              element["domain"] === window.csqita.siteUrl;
            chatbotsD = [...chatbotsD, element];
            if (element["domain"] === window.csqita.siteUrl) {
              ActivateCardRequest(element.id);
            }
          } else {
            element["domain"] = "";
            element["status"] = "deactive";
            element["available"] = true;
            chatbotsD = [...chatbotsD, element];
          }
          if (check["chatbot"] === null && element["available"]) {
            check["chatbot"] = element;
          }
          console.log();
        }
        if (!check.isActive && check["chatbot"] && check["chatbot"].id) {
          ActivateCardRequest(check["chatbot"].id);
          var chatbot = chatbotsD.find(function (c) {
            return c.id === check["chatbot"].id;
          });
          chatbot.status = "active";
          chatbot.domain = window.csqita.siteUrl;
          chatbot.available = true;
        }
        console.log(chatbotsD);
        resolve({
          chatbots: chatbotsD,
        });
        return;
      }
      reject(new Error("Authentication failed"));
    });
  }

  // In-memory stand-in for server state.
  var DUMMY_SERVER_CARDS = [
    { id: "card-1", name: "Quota Sync", status: "active" },
    { id: "card-2", name: "Traffic Insights", status: "deactive" },
    { id: "card-3", name: "Cache Accelerator", status: "deactive" },
    { id: "card-4", name: "SEO Autopilot", status: "deactive" },
    { id: "card-5", name: "Backup Guardian", status: "deactive" },
    { id: "card-6", name: "Spam Shield", status: "deactive" },
  ];

  // -----------------------------------------------------------------
  // Token helpers
  // -----------------------------------------------------------------

  function getToken() {
    return window.csqita.token;
  }

  function setToken(token, name, user_identifier) {
    wp.apiFetch({
      path: `${window.csqita.internalEndpoint}/user/save`,
      method: "POST",
      data: {
        nonce: window.csqita.nonce,
        token: token,
        name: name,
        user_identifier: user_identifier,
      },
    }).then((newPost) => {
      console.log("Post user save done with status", newPost.code);
      window.location.reload();
    });
  }

  function clearToken() {
    localStorage.removeItem(TOKEN_KEY);
  }

  // -----------------------------------------------------------------
  // Root render / routing
  // -----------------------------------------------------------------

  function render() {
    var token = getToken();
    if (token) {
      if (rootEl) rootEl.innerHTML = "";
      renderDashboard();
    } else {
      if (dashboardEl) dashboardEl.innerHTML = "";
      renderLogin();
    }
  }

  // -----------------------------------------------------------------
  // Login page
  // -----------------------------------------------------------------

  function renderLogin() {
    rootEl.innerHTML =
      '<div class="csqita-login-shell">' +
      '<div class="csqita-login-card">' +
      '<div class="csqita-brand-mark">CQ</div>' +
      "<h1>Sign in to CSQITA</h1>" +
      '<p class="csqita-login-sub">Enter your credentials to manage your plugin cards.</p>' +
      '<form id="csqita-login-form" novalidate>' +
      '<div class="csqita-field">' +
      '<label for="csqita-username">Username</label>' +
      '<input type="text" id="csqita-username" name="username" autocomplete="username" placeholder="e.g. admin">' +
      "</div>" +
      '<div class="csqita-field">' +
      '<label for="csqita-password">Password</label>' +
      '<input type="password" id="csqita-password" name="password" autocomplete="current-password" placeholder="••••••••">' +
      "</div>" +
      '<button type="submit" class="csqita-submit" id="csqita-login-btn">' +
      '<span class="csqita-spinner"></span>' +
      '<span class="csqita-submit-label">Sign in</span>' +
      "</button>" +
      '<div class="csqita-error" id="csqita-login-error"></div>' +
      "</form>" +
      '<p class="csqita-hint">do not have account? <a src="https://csqita.com/signup">register here</a></p>' +
      '<p class="csqita-hint"><a src="'+window.csqita.privacyPolicy+'">Privacy and Policy</a></p>' +
      '<p class="csqita-hint"><a src="'+window.csqita.termsOfService+'">Terms of Service</a></p>' +
      "</div>" +
      "</div>";

    var form = document.getElementById("csqita-login-form");
    var btn = document.getElementById("csqita-login-btn");
    var errorBox = document.getElementById("csqita-login-error");

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      var username = document.getElementById("csqita-username").value.trim();
      var password = document.getElementById("csqita-password").value;

      errorBox.classList.remove("is-visible");
      errorBox.textContent = "";
      btn.classList.add("is-loading");
      btn.disabled = true;

      LoginRequest(username, password)
        .then(function (res) {
          setToken(res.token, res.name, res.userid);
          window.location.reload();
        })
        .catch(function (err) {
          errorBox.textContent =
            err.message || "Login failed. Please try again.";
          errorBox.classList.add("is-visible");
        })
        .finally(function () {
          btn.classList.remove("is-loading");
          btn.disabled = false;
        });
    });
  }

  // -----------------------------------------------------------------
  // Dashboard page
  // -----------------------------------------------------------------

  function renderDashboard() {
    dashboardEl.innerHTML =
      '<div class="csqita-dashboard-shell">' +
      '<div class="csqita-topbar">' +
      '<div class="csqita-topbar-left">' +
      '<div class="csqita-brand-mark">CQ</div>' +
      "<div>" +
      "<h1>CSQITA Cards</h1>" +
      '<div class="csqita-topbar-sub">Manage which cards are active</div>' +
      "</div>" +
      "</div>" +
      '<button class="csqita-logout" id="csqita-logout-btn">Log out</button>' +
      "</div>" +
      '<div class="csqita-dashboard-body">' +
      '<div class="csqita-dashboard-error" id="csqita-dashboard-error"></div>' +
      '<div class="csqita-dashboard-heading">' +
      "<h2>Cards</h2>" +
      '<span class="csqita-count" id="csqita-card-count"></span>' +
      "</div>" +
      '<div class="csqita-card-grid" id="csqita-card-grid">' +
      '<div class="csqita-loading-row">Loading cards…</div>' +
      "</div>" +
      "</div>" +
      "</div>";

    document
      .getElementById("csqita-logout-btn")
      .addEventListener("click", function () {
        wp.apiFetch({
          path: `${window.csqita.internalEndpoint}/user/logout`,
          method: "GET",
        }).then((newPost) => {
          console.log("Post user save done with status", newPost.code);
          window.location.reload();
        });
        render();
      });

    loadCards();
  }

  function loadCards() {
    var grid = document.getElementById("csqita-card-grid");
    var errorBox = document.getElementById("csqita-dashboard-error");

    GetCardsRequest()
      .then(function (cards) {
        console.log(cards);
        errorBox.classList.remove("is-visible");
        renderCardGrid(cards);
      })
      .catch(function (err) {
        console.log(err);
        grid.innerHTML = "";
        errorBox.textContent = err.message || "Could not load cards.";
        errorBox.classList.add("is-visible");
      });
  }

  function renderCardGrid(cards) {
    const cardss = cards.chatbots;
    var grid = document.getElementById("csqita-card-grid");
    var count = document.getElementById("csqita-card-count");

    count.textContent = cardss.length + " total";

    if (!cardss.length) {
      grid.innerHTML = '<div class="csqita-empty-row">No cards found.</div>';
      return;
    }

    grid.innerHTML = cardss
      .map(function (card) {
        console.log(card);
        var isActive = card.status === "active";
        return (
          '<div class="csqita-card" data-status="' +
          card.status +
          '" data-card-id="' +
          card.id +
          '">' +
          '<div class="csqita-card-name">' +
          escapeHtml(card.name) +
          "</div>" +
          (card.available
            ? '<span class="csqita-status" data-status="' +
              card.status +
              '">' +
              '<span class="csqita-status-dot"></span>' +
              (isActive ? "Active" : "Deactive") +
              "</span>" +
              (isActive
                ? ""
                : '<div class="csqita-card-footer">' +
                  '<button class="csqita-activate-btn" data-card-id="' +
                  card.id +
                  '">' +
                  '<span class="csqita-spinner"></span>' +
                  '<span class="csqita-activate-label">Activate</span>' +
                  "</button>" +
                  "</div>")
            : '<span class="csqita-status" data-status="' +
              card.status +
              '">' +
              escapeHtml("used for " +
              card.domain +
              ", please deactive it from " +
              card.domain +
              " before use it for this domain. you can deactive it from csqita.com by fill the domain as blank.") +
              "</span>" +
              "</div>") + "</div>"
        );
      })
      .join("");

    var activateButtons = grid.querySelectorAll(".csqita-activate-btn");
    activateButtons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        handleActivate(btn.getAttribute("data-card-id"));
      });
    });
  }

  function handleActivate(cardId) {
    var btn = document.querySelector(
      '.csqita-activate-btn[data-card-id="' + cardId + '"]',
    );
    var errorBox = document.getElementById("csqita-dashboard-error");

    if (btn) {
      btn.disabled = true;
      btn.classList.add("is-loading");
    }

    ActivateCardRequest(cardId)
      .then(function () {
        return GetCardsRequest();
      })
      .then(function (cards) {
        const cardss = cards.chatbots;
        for (let index = 0; index < cardss.length; index++) {
          const element = cardss[index];
          if (element.id !== cardId && element.status === "active") {
            DeactivateCardRequest(element.id);
            element.status = "deactive";
            element.domain = "";
            element["available"] = true;
          }
        }
        errorBox.classList.remove("is-visible");
        renderCardGrid(cards);
      })
      .catch(function (err) {
        errorBox.textContent = err.message || "Could not activate card.";
        errorBox.classList.add("is-visible");
        if (btn) {
          btn.disabled = false;
          btn.classList.remove("is-loading");
        }
      });
  }

  function escapeHtml(str) {
    var div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
  }

  // -----------------------------------------------------------------
  // Init
  // -----------------------------------------------------------------

  render();
})();
