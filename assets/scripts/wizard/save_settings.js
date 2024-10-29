// let mail_sent = false;
// let license_active = false;

/** @type{HTMLButtonElement | null} */
const activate_button = document.querySelector(".activate");
if (activate_button) {
  activate_button.addEventListener("click", async () => {
    await activate();
  });

  // if we are on a page with the settings/licensing options, save the options
  /** @type{HTMLAnchorElement | null} */
  const next_link = document.querySelector("#next-link");
  if (next_link) {
    next_link.addEventListener("click", async (e) => {
      e.preventDefault();

      await save_settings();

      document.location.href = next_link.href;
    });
  }
}

// async function send_mail() {
//   if (mail_sent) {
//     console.warn("Mail has been sent already");
//     return;
//   }

//   const response = await fetch(almefy_api + "device/add", {
//     method: "post",
//     body: JSON.stringify({ user_id }),
//     headers: {
//       "Content-Type": "application/json",
//       "X-WP-NONCE": nonce,
//     },
//   });

//   if (response.ok) mail_sent = true;

//   console.log("Mail sent:", response);
// }

async function save_settings() {
  const enable_almefy = document.querySelector("#almefy-api-enabled").checked;
  const enable_registration = document.querySelector(
    "#almefy-api-registration"
  ).checked;
  const redirect = document.querySelector("#almefy-api-redirect").value;
  const enable_sandbox = document.querySelector(
    "#almefy-sandbox-enabled"
  ).checked;
  // const code_type = document.querySelector("#code_type").value;
  // const code_type = 'qr';

  const response = await fetch(almefy_api + "wizard/settings", {
    method: "post",
    body: JSON.stringify({
      enable_almefy,
      enable_registration,
      // code_type,
      redirect,
      enable_sandbox,
    }),
    headers: {
      "Content-Type": "application/json",
      "X-WP-NONCE": nonce,
    },
  });

  console.log("save settings", response);
}

async function activate() {
  const key = document.querySelector("#api_key");
  const secret = document.querySelector("#api_secret");
  /** @type{HTMLButtonElement} */
  const button = document.querySelector(".activate");

  const response = await fetch(almefy_api + "verify_credentials", {
    method: "post",
    body: JSON.stringify({
      key: key.value,
      secret: secret.value,
    }),
    headers: {
      "Content-Type": "application/json",
      "X-WP-NONCE": nonce,
    },
  });

  if (response.ok) {
    // license_active = true;
    button.disabled = true;
    button.textContent = licence_active_text;
    button.style.background = "var(--green)";
    button.style.color = "white";
    button.style.border = "none";
    button.style.opacity = "1";
    document.querySelector("#next").disabled = false;
  } else {
    // license_active = false;
    button.disabled = false;
    button.textContent = licence_bad_text;
    button.style.background = "var(--red-dark)";
    button.style.color = "white";
    button.style.border = "none";
    button.style.opacity = "1";
    document.querySelector("#next").disabled = true;
  }
  console.log("license activation", response);
}
