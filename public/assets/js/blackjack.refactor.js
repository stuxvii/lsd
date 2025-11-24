const dommy = {
    balance: document.getElementById('amount_of_pocket_money'),
    bet_display: document.getElementById('money_on_the_line'),
    status_message: document.getElementById('start_status_span'),
    player_hand: document.getElementById('your_hand'),
    dealer_hand: document.getElementById('dealer_hand'),
    song: document.getElementById("song"),
    drawing: document.getElementById("do_you_like_my_drawing"),
    forfeit_button: document.getElementById("forfeit"),
    init_button: document.getElementById("init"),
    draw_button: document.getElementById("draw"),
    stand_button: document.getElementById("stand"),
    unmute_button: document.getElementById("unmute"),
    begin_game_div: document.getElementById("begin_game"),
    bet_amount_input: document.getElementById('amount'),
};

const BASE_URL = '/casino/game/blackjack';

let money = 0;
let player_status = "playing";
let dealer_status = "playing";

dommy.song.volume = 0.2;

async function send_game_action(endpoint, postData = new URLSearchParams()) {
    postData.append('csrftoken', csrftoken);
    const url = `${BASE_URL}${endpoint}`;
    const response = await fetch(url, {
        method: "POST",
        body: postData
    });

    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.message || 'Server error occurred.');
    }
    return data;
}

function show_card(num, el) {
    const card = document.createElement("span");
    card.classList.add("card");
    card.textContent = String(num);
    el.append(card);
}

function play_song() {
    dommy.song.play();
    dommy.unmute_button.onclick = null;
    dommy.unmute_button.removeAttribute("href");
    dommy.unmute_button.innerHTML = "Playing: Ternary Game - Nine Hours, Nine Persons, Nine Doors (SiIvagunner Remix)";
}

function handle_ending(result) {
    let message = '';

    switch (result) {
        case "push":
            message = "Tie! Money has been given back.";
            dommy.dealer_hand.className = "fr stand";
            dommy.player_hand.className = "fr stand";
            break;
        case "lost":
            message = "You've lost.";
            dommy.player_hand.className = "fr fade_gone";
            break;
        case "won":
            message = "You're Winnar!";
            dommy.dealer_hand.className = "fr fade_gone";
            break;
        default:
            message = "";
            break;
    }

    if (message != '') {
        dommy.bet_display.textContent = message;
        if (result != "lost") {
            const a = new Audio("/assets/audio/win.ogg");
            a.play();
        } else {
            const a = new Audio("/assets/audio/lose.opus");
            a.play();
        }
    }
}

async function init_game_view(bet_amount) {
    if (bet_amount !== undefined) {
        money = bet_amount;
    }

    play_song();

    dommy.bet_display.textContent = `¥${money} on the line`;
    dommy.drawing.classList.remove("hidden");
    dommy.begin_game_div?.remove();

    try {
        const response = await fetch(`${BASE_URL}/cards`);
        const data = await response.json();

        const [player_cards, dealer_cards, statuses] = data;

        if (dealer_status != "playing") {
            dommy.dealer_hand.className = "fr stand";
        }
        if (player_status != "playing") {
            dommy.player_hand.className = "fr stand";
        }

        if (player_status != "playing" || dealer_status != "playing") {
            if (player_status === "push" || player_status === "lost" || dealer_status === "lost") {
                handle_ending(player_status === "push" ? "push" : player_status === "lost" ? "lost" : "won");
                return
            }
        }

        player_cards[0].forEach((num, i) => {
            setTimeout(() => {
                show_card(num, dommy.player_hand);
            }, i * 500);
        });

        dealer_cards[0].forEach((num, i) => {
            setTimeout(() => {
                show_card(num, dommy.dealer_hand);
            }, i * 500);
        });

        player_status = statuses[0];
        dealer_status = statuses[1];
        handle_ending(player_status);
    } catch(e) {
        console.error(e);
    }
}

dommy.init_button.addEventListener('click', async function () {
    const amount = dommy.bet_amount_input.value || 0;
    this.disabled = true;

    const postData = new URLSearchParams();
    postData.append('amount', amount);

    try {
        const data = await send_game_action('/init', postData);
        if (data.player_status) {
            money = data.money;
            init_game_view();
        } else {
            dommy.status_message.textContent = data.message;
        }
    } catch (e) {
        console.error(e);
    } finally {
        this.disabled = false;
    }
});

dommy.draw_button.addEventListener('click', async function() {
    if (player_status !== "playing") {
        return;
    }

    try {
        const data = await send_game_action('/draw');
        const [player_card, dealer_card] = data[0];
        const [new_player_status, new_dealer_status] = data[2];

        show_card(player_card, dommy.player_hand);
        player_status = new_player_status;
        dealer_status = new_dealer_status;

        if (player_status === "stand") {
            dommy.player_hand.className = "fr stand";
        } else if (player_status != "playing") {
            dommy.player_hand.className = "fr fade_gone";
            handle_ending(player_status)
        }

        if (dealer_status === "playing") {
            setTimeout(() => {
                show_card(dealer_card, dommy.dealer_hand);
                dealer_status = new_dealer_status;

                if (dealer_status === "stand") {
                    dommy.dealer_hand = "fr stand";
                } else if (dealer_status === "lost") {
                    dommy.dealer_hand.className = "fr fade_gone";
                    handle_ending("won");
                }
            }, 500);
        } else {
            dommy.dealer_hand.className = "fr stand";
        }
    } catch (e) {
        console.error(e);
    }
})

dommy.stand_button.addEventListener('click', async function() {
    if (player_status != "playing") {
        return;
    }

    try {
        const data = await send_game_action('/stand');
        const [_z, dealer_cards, statuses] = data;
        const [new_player_status, new_dealer_status] = statuses;

        player_status = new_player_status;
        dealer_status = new_dealer_status;

        dommy.player_hand = "fr stand";

        dommy.dealer_hand.innerHTML = "";
        if (dealer_cards[0] && dealer_cards[0].length > 0) {
            dealer_cards[0].forEach((num,i) => {
                show_card(num, dommy.dealer_hand)
            })
        }

        if (player_status != "playing") {
            handle_ending(player_status);
        } else if (dealer_status === "lost") {
            handle_ending("won");
        }

    } catch(e) {
        console.error(e);
    }
})

dommy.forfeit_button.addEventListener('click', async function () {
    const ongoing_game = (player_status === "playing" || dealer_status === "playing");

    if (!ongoing_game|| confirm("Are you sure you want to restart the current game?\nYou will lose the money you betted.")) {
        try {
            await send_game_action('/forfeit');
            location.reload();
        } catch (e) {
            console.error("Forfeit failed:", e);
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    fetch(`${BASE_URL}/status`)
    .then(response => response.json())
    .then(data => {
        if (data.player_status) {
            init_game_view(data.money);
        }
    })
    .catch(e => console.error(e));

    if (dommy.song.paused) {
        dommy.unmute_button.onclick = function() { play_song(); };
        dommy.unmute_button.href = "#";
        dommy.unmute_button.textContent = "unmute";
    }
})