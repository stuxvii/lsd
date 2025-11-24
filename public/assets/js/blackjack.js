const balance = document.getElementById('amount_of_pocket_money');
const money_on_the_line = document.getElementById("money_on_the_line");
const start_status_span = document.getElementById('start_status_span');
const your_hand = document.getElementById('your_hand');
const dealer_hand = document.getElementById('dealer_hand');
const song = document.getElementById("song");
const cute_image = document.getElementById("do_you_like_my_drawing");
const forfeit_button = document.getElementById("forfeit");
const init_button = document.getElementById("init");
const draw_button = document.getElementById("draw");
const stand_button = document.getElementById("stand");

var dealer_status = "playing";
var player_status = "playing";

let money = 0;
song.volume = 0.2;

function play_song() {
    song.play();
    const button = document.getElementById("unmute");
    button.onclick = null;
    button.removeAttribute("href");
    button.innerHTML = "Playing: Ternary Game - Nine Hours, Nine Persons, Nine Doors (SiIvagunner Remix)";
}

document.addEventListener('DOMContentLoaded', function () {
    fetch('/casino/game/blackjack/status').then(response => {
        return response.json().then(
            data => {
            if (data.player_status) {
                money = data.money;
                init_game_view(data.money);
            }
            }
        )
    }

    )
    if (song.paused) {
        const unmute = document.getElementById("unmute");
        unmute.onclick = function () { play_song(); };
        unmute.href = "#";
        unmute.innerHTML = "unmute";
    }
})

forfeit_button.addEventListener('click', async function () {
    if ((player_status == "push" || player_status == "won" || player_status == "lost") || confirm("Are you sure you want to restart the current game?\nYou will lose the money you betted.")) {
        const postData = new URLSearchParams();
        postData.append('csrftoken', csrftoken);
        const r = await fetch('/casino/game/blackjack/forfeit', { method: "POST", body: postData })
        if (r.ok) {
            location.reload()
        }
    }
})

function show_card(num, el) {
    const card = document.createElement("span");
    card.classList.add("card");
    card.textContent = String(num);
    el.append(card);
}

async function init_game_view() {
    const postData = new URLSearchParams();
    postData.append('csrftoken', csrftoken);
    money_on_the_line.textContent = "¥" + String(money) + " on the line";
    start_status_span.textContent = "OK";
    play_song()
    document.querySelector("#begin_game").remove();
    cute_image.classList.remove("hidden");
    fetch('/casino/game/blackjack/cards').then(
        async response => {
            const data = await response.json();
            if (data[2][1] != "playing") {
                dealer_hand.className = "fr stand";
            }
            if (data[2][0] != "playing") {
                your_hand.className = "fr stand";
            }

            if (data[2][0] == "push") {
                dealer_hand.className = "fr fade_gone";
                const r = await fetch('/casino/game/blackjack/forfeit', { method: "POST", body: postData })
                if (r.ok) {
                    location.reload()
                }
            }
            if (data[2][1] == "lost") {
                dealer_hand.className = "fr fade_gone";
                const r = await fetch('/casino/game/blackjack/forfeit', { method: "POST", body: postData })
                if (r.ok) {
                    location.reload()
                }
            }
            if (data[2][0] == "lost") {
                your_hand.className = "fr fade_gone";
                const r = await fetch('/casino/game/blackjack/forfeit', { method: "POST", body: postData })
                if (r.ok) {
                    location.reload()
                }
            }
            data[0][0].forEach((num, i) => {
                const delay = i * 500
                setTimeout(() => { show_card(num, your_hand) }, delay);
            });
            data[1][0].forEach((num, i) => {
                console.log(num);
                const delay = i * 500
                setTimeout(() => { show_card(num, dealer_hand) }, delay);
            });
            player_status = data[2][0];
            dealer_status = data[2][1];
        }
    )
}

stand_button.addEventListener('click', async function () {
    const postData = new URLSearchParams();
    postData.append('csrftoken', csrftoken);
    if (player_status != "playing") {
        return
    }
    fetch('/casino/game/blackjack/stand', { method: "POST", body: postData }).then(
        async response => {
            const data = await response.json();
            your_hand.className = "fr stand";
            your_hand.className = "fr fade_gone";

            if (data[2][0] == "push") {
                dealer_hand.className = "fr fade_gone";
                money_on_the_line.textContent = "Tie! Money has been given back. Press Restart to play again."
            }

            if (data[2][0] == "lost") {
                dealer_hand.className = "fr fade_gone";
                money_on_the_line.textContent = "You've lost. Press Restart to play again."
            }

            if (data[2][1] == "stand") {
                dealer_hand.className = "fr stand";
            }
            if (data[2][1] == "lost") {
                dealer_hand.className = "fr fade_gone";
                money_on_the_line.textContent = "You've won! Press Restart to play again."
            }

            if (dealer_status == "playing") {
                data[1][0].forEach((num, i) => {
                    show_card(data[1][0], dealer_hand);
                    const delay = i * 500
                    setTimeout(() => { show_card(num, dealer_hand) }, delay);
                })
                dealer_status = data[2][1]
            }
        }
    )
})

draw_button.addEventListener('click', async function () {
    const postData = new URLSearchParams();
    postData.append('csrftoken', csrftoken);
    if (player_status != "playing") {
        return
    }
    fetch('/casino/game/blackjack/draw', { method: "POST", body: postData }).then(
        async response => {
            const data = await response.json();
            if (data[2][0] == "stand") {
                your_hand.className = "fr stand";
            }
            if (data[2][0] == "lost") {
                your_hand.className = "fr fade_gone";
                money_on_the_line.textContent = "You've lost. Press Restart to play again."
            }

            if (data[2][0] == "push") {
                dealer_hand.className = "fr fade_gone";
                money_on_the_line.textContent = "Tie! Money has been given back. Press Restart to play again."
            }

            if (player_status == "playing") {
                show_card(data[0][0], your_hand);
                player_status = data[2][0]
            }
            if (dealer_status == "playing") {
                setTimeout(async () => {
                    show_card(data[1][0], dealer_hand)
                    if (data[2][1] == "lost") {
                        dealer_hand.className = "fr fade_gone";
                        money_on_the_line.textContent = "You've won! Press Restart to play again."
                    }
                    if (data[2][1] == "stand") {
                        dealer_hand.className = "fr stand";
                    }
                }, 500);
                dealer_status = data[2][1]
            }
        }
    )
})

init_button.addEventListener('click', function () {
    const amount_money = document.getElementById('amount').value || 0;
    this.disabled = true;
    const postData = new URLSearchParams();
    postData.append('amount', amount_money);
    postData.append('csrftoken', csrftoken);

    fetch('/casino/game/blackjack/init', {
        method: 'POST',
        body: postData
    })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'Server error occurred.');
                }
                return data;
            });
        })
        .then(data => {
            if (data.player_status) {
                money = data.money;
                init_game_view();
            } else {
                start_status_span.textContent = data.message;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
        })
        .finally(() => {
            this.disabled = false;
        });
}
)
