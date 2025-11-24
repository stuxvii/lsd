<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["csrftoken"]) || $_POST["csrftoken"] != $_SESSION["csrftoken"]) {
        die(json_encode(["status" => "error", "message" => "Invalid CSRF token..."]));
    }

    if (!isset($_POST["amount"]) || $_POST["amount"] < 1) {
        die(json_encode(["status" => "error", "message" => "You must bet an amount higher than ¥0!"]));
    }
    
    if (!is_numeric($_POST["amount"])) {
        die(json_encode(["status" => "error", "message" => "Your bet must be a number gang."]));
    }

    if ($_POST["amount"] > $this->economy["pocket_money"]) {
        die(json_encode(["status" => "error", "message" => "You cannot afford to bet this high!"]));
    }

    if (!isset($_POST["side"])) {
        die(json_encode(["status" => "error", "message" => "You must pick a side to bet on!"]));
    }

    switch ($_POST["side"]) {
        case "bath":
            $side = 1;
            break;
        case "life":
            $side = 0;
            break;
        default:
            die(json_encode(["status" => "error", "message" => "Invalid side. Must be either `bath` or `life`"]));
            break;
    }
    $fate = random_int(0,1);

    $stmt_charge = $this->db->prepare('UPDATE economy SET pocket_money = pocket_money - ? WHERE id = ?');
    $stmt_charge->execute([$_POST["amount"], $this->user_info['id']]);

    if ($side == $fate) {
        $reimburse = $_POST["amount"] * 2;
        $stmt_reimburse = $this->db->prepare('UPDATE economy SET pocket_money = pocket_money + ? WHERE id = ?');
        $stmt_reimburse->execute([$reimburse, $this->user_info['id']]);

        $stmt_getmoney = $this->db->prepare('SELECT pocket_money FROM economy WHERE id = ?');
        $stmt_getmoney->execute([$this->user_info['id']]);
        $new_money = $stmt_getmoney->fetch()["pocket_money"];
        die(json_encode(["status" => "success", "message" => "You're winner!", "money" => $new_money, "sogged" => $fate]));
    } else {
        $stmt_getmoney = $this->db->prepare('SELECT pocket_money FROM economy WHERE id = ?');
        $stmt_getmoney->execute([$this->user_info['id']]);
        $new_money = $stmt_getmoney->fetch()["pocket_money"];
        die(json_encode(["status" => "success", "message" => "SORRY NOTHING", "money" => $new_money, "sogged" => $fate]));
    }
    exit;
}
?>
<audio id="song" autoplay loop src="/assets/audio/roulette_theme.opus"></audio>
<audio id="splash" src="/assets/audio/bath.opus"></audio>
<audio id="eh" src="/assets/audio/eh.opus"></audio>
<a href="#" id="unmute"></a>
<script id="script">
    const song = document.querySelector("#song");
    song.volume = 0.2;
    
    function play_song() {
        song.play();
        const button = document.querySelector("#unmute");
        button.onclick = null;
        button.removeAttribute("href");
        button.innerHTML = "Playing: Super Smash Bros. 64 - Target Practice";
    }


    document.addEventListener('DOMContentLoaded', function() {
        if (song.paused) {
            const unmute = document.querySelector("#unmute");
            unmute.onclick = function() { play_song(); };
            unmute.href = "#";
            unmute.innerHTML = "unmute";
        }
    })
</script>
<div class="fc">
    <span>
        ¥
        <span id="amount_of_pocket_money">
            <?=$this->economy["pocket_money"]?>
        </span> 
        remaining
    </span>
    <span id="status_span">oh yeah woo yeah oh yeah woo woo yeah woo woo oh</span>
</div>
<img height="200" width="200" id="cat" src="/assets/images/drycat.png">
<div class="fr">
    <input type="number" placeholder="0" value="0" id="amount" min="0">
    <button onclick="bet('bath')" style="background-color:var(--evil)">Bath</button>
    <button onclick="bet('life')">Life</button>
</div>
    <span>gamble on if the cat is gonna get sogged</span>
<script>

const balance = document.getElementById('amount_of_pocket_money');
const status_span = document.getElementById('status_span');
const cat = document.getElementById('cat');
const bath_sfx = document.getElementById('splash');
const eh_sfx = document.getElementById('eh');

function bet(side) {
    const amount_money = document.getElementById('amount').value || 0;
    if (!side) {
        console.error("You need to guess if the cat is gonna be `bath`ed or `life`d");
        return;
    }
    this.disabled = true;
    const postData = new URLSearchParams();
    postData.append('side', side);
    postData.append('amount', amount_money);
    postData.append('csrftoken', csrftoken);

    fetch('/casino/game/roulette', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
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
        if (data.status === 'success') {
            if (data.sogged == 0) {
                cat.src = "/assets/images/drycat.png";
                eh_sfx.play();
            } else {
                cat.src = "/assets/images/soggycat.webp";
                bath_sfx.play();
            }
            balance.textContent = data.money;
            status_span.textContent = data.message;
        } else {
            status_span.textContent = data.message;
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
    })
    .finally(() => {
        this.disabled = false;
    });
}

</script>