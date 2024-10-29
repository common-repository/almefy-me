

const almefy_widgets = document.querySelectorAll('.almefy-me-login-widget');
for(const widget of almefy_widgets) {
    const api = widget.getAttribute('data-api');
    const nonce = widget.getAttribute('data-nonce');
    initAlmefyLogin(api, nonce, widget);
}

// Initializes the re-connect and register popup in login widgets.
function initAlmefyLogin(almefy_api, nonce, container) {
    // const container = document.querySelector("#" + container_id);

    if (container) {
        // Connect
        const overlay_connect = container.querySelector(".almefy-login-connect");
        const button_open_connect = container.querySelector(".almefy-login-btn-connect");

        if (button_open_connect && overlay_connect) {

            button_open_connect.addEventListener("click", (e) => {
                e.preventDefault();
                overlay_connect.classList.add("active");
            });

        } else {
            // console.log("Almefy: Connect overlay missing elements.");
        }
        
        // Register
        const overlay_register = container.querySelector(".almefy-login-register");
        const open_register_buttons = container.querySelectorAll(".almefy-login-btn-register");
    
        if (open_register_buttons.length > 0 && overlay_register) {
            for(const open_register_button of open_register_buttons) {
                open_register_button.addEventListener("click", (e) => {
                  e.preventDefault();
                  overlay_register.classList.add("active");
                });
            }
        } else {
        //   console.log("Almefy: Register overlay missing elements.");
        }

        // Register
        const overlay_messages = container.querySelector(".almefy-login-message");
        // @ts-ignore
        const message = overlay_messages.querySelector(".almefy-login-banner");

        // Close overlays
        const close_buttons = container.querySelectorAll(".almefy-login-close");

        for(const close_button of close_buttons) {
            close_button.addEventListener("click", (e) => {
                e.preventDefault();
                if(overlay_connect) overlay_connect.classList.remove("active");
                if(overlay_register) overlay_register.classList.remove("active");
                if(overlay_messages) overlay_messages.classList.remove("active");

                // TODO: hide result banner
            });
        }

        // Send Connect Mail
        const request_connect_button = container.querySelector('.almefy-request-connect-btn');
        const connect_input = container.querySelector('.almefy-login-connect-input');

        if(request_connect_button && connect_input) {
            request_connect_button.addEventListener('click', async (e) => {
                e.preventDefault();
                
                if (!connect_input.checkValidity()) {
                    connect_input.reportValidity();
                    return;
                }


                const response = await fetch(almefy_api + "device/reconnect", {
                  method: "post",
                  body: JSON.stringify({ user_login: connect_input.value }),
                  headers: {
                    "Content-Type": "application/json",
                    "X-WP-NONCE": nonce,
                  },
                });

                if(response.ok) {
                    // @ts-ignore
                    overlay_messages.classList.add('active');
                    // @ts-ignore
                    message.classList.remove("almefy-login-banner-error");
                    // @ts-ignore
                    message.innerText = `${almefy_local.mail_sent} ${connect_input.value}!`;

                    // @ts-ignore
                    connect_input.value = '';
                } else {
                    const err = await response.json();
                    console.log(err);
                    
                    // @ts-ignore
                    overlay_messages.classList.add('active');
                    // @ts-ignore
                    message.classList.add("almefy-login-banner-error");
                    // @ts-ignore
                    message.innerText = err;
                }
            });
        }

        // Create Account

        const register_button = container.querySelector(".almefy-register-button");
        const register_username_input = container.querySelector(".almefy-login-register-username");
        const register_email_input = container.querySelector(".almefy-login-register-email");

        if(register_button && register_username_input && register_email_input) {
            register_button.addEventListener('click', async (e) => {
                e.preventDefault();
                
                if (!register_username_input.checkValidity()) {
                    register_username_input.reportValidity();
                    return;
                }
                
                if (!register_email_input.checkValidity()) {
                    register_email_input.reportValidity();
                    return;
                }

                const username = register_username_input.value;
                const email = register_email_input.value;

                const response = await fetch(almefy_api + "register", {
                    method: "post",
                    body: JSON.stringify({username, email}),
                    headers: { 
                        "Content-Type": "application/json" ,
                        "X-WP-NONCE": nonce,
                    },
                });

                if (response.ok) {
                    // @ts-ignore
                    overlay_messages.classList.add("active");
                    // @ts-ignore
                    message.classList.remove("almefy-login-banner-error");
                    // @ts-ignore
                    message.innerText = `${almefy_local.mail_sent} ${register_email_input.value}!`;

                    // @ts-ignore
                    register_username_input.value = "";
                    // @ts-ignore
                    register_email_input.value = "";
                } else {
                    const err = await response.json();
                    console.log(err);

                    // @ts-ignore
                    overlay_messages.classList.add("active");
                    // @ts-ignore
                    message.classList.add("almefy-login-banner-error");
                    // @ts-ignore
                    message.innerText = err;
                }
            });
        }

    }   
}

//   async function setup() {

//     const send_mail_buttons = document.querySelectorAll(
//       ".almefy-send-mail"
//     );

//     for (const button of send_mail_buttons) {
//       button.addEventListener("click", async (event) => {
//         event.preventDefault();

//         const success_notification = document.querySelector(
//             ".almefy-success-notification > p"
//           );
//         const error_notification = document.querySelector(
//             ".almefy-error-notification"
//           );

//         success_notification.style.display = 'none';
//         error_notification.style.display = 'none';
        
//         const old_label = button.innerText;
//         button.innerText = "...";
//         const response = await fetch(almefy_api + "device/add", {
//           method: "post",
//           body: JSON.stringify({ user_id }),
//           headers: {
//             "Content-Type": "application/json",
//             "X-WP-NONCE": insta_nonce,
//           },
//         });

//         button.innerText = old_label;


//         // const info_area = document.querySelector(".almefy-info-area");
//         if (response.ok) {
//           const json = await response.json();

//           /** @type{HTMLDivElement} */

//           success_notification.style.display = "block";

//           success_notification.innerHTML = `<p>${__(
//             "Email has been sent to",
//             "almefy-me"
//           )} <b>${json.sent_to}</b> !</p>`;

//           // info_area.appendChild(box);
//         } else {
//           const body = await response.text();

//           /** @type{HTMLDivElement} */
//           error_notification.style.display = "block";

//           console.error("almefy: could not send mail", response);
//           error_notification.innerHTML = `${__(
//             "Email could not be sent!",
//             "almefy-me"
//           )}!<br> ${__(
//             "Please try again later or contact an administrator",
//             "almefy-me"
//           )}.<br>${body}`;

//           console.error(response);
//         }
//       });
//     }
//   }

//   setup();
// }
