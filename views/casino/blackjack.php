<?php
$deck = [
    1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 10, 10, 11,
    1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 10, 10, 11,
    1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 10, 10, 11,
    1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 10, 10, 11,
];

function draw_card($amount_of_cards = [0]) {
    $this_card = array_shift($_SESSION["casino_blackjack_deck"]);
    if (in_array(11, $amount_of_cards) && $this_card == 11) {
        $this_card = 1;
    }
    return $this_card;
};

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["csrftoken"]) || $_POST["csrftoken"] != $_SESSION["csrftoken"]) {
        die(json_encode(["status" => "bad", "message" => "Invalid CSRF token..."]));
    }

    switch ($args) {
        case 'init':
            if (!isset($_SESSION["casino_blackjack_player_status"]) ) {
                if (!isset($_POST["amount"]) || $_POST["amount"] < 1) {
                    die(json_encode(["status" => "bad", "message" => "You must bet an amount higher than ¥0!"]));
                } else {
                    if ($this->economy["pocket_money"] < $_POST["amount"]) {
                        die(json_encode(["status" => "bad", "message" => "You cannot afford to bet this high"]));
                    }
                    $_SESSION["casino_blackjack_bet"] = (int) $_POST["amount"];
                    $_SESSION["casino_blackjack_deck"] = $deck;
                    shuffle($_SESSION["casino_blackjack_deck"]);
                    // we call it with no argument here bc the hands are not initialized 
                    $_SESSION["casino_blackjack_your_hand"] = [draw_card(), draw_card()];
                    $_SESSION["casino_blackjack_deal_hand"] = [draw_card(), draw_card()];
                    $_SESSION["casino_blackjack_player_status"] = "playing";
                    $_SESSION["casino_blackjack_dealer_status"] = "playing";

                    $fee = $_SESSION["casino_blackjack_bet"];
                    $stmt_fee = $this->db->prepare('UPDATE economy SET pocket_money = pocket_money - ? WHERE id = ?');
                    $stmt_fee->execute([$fee, $this->user_info['id']]);

                    if (array_sum($_SESSION["casino_blackjack_your_hand"]) == 21) {
                        if (array_sum($_SESSION["casino_blackjack_your_hand"]) == array_sum($_SESSION["casino_blackjack_deal_hand"])) { // we're 100% sure the player's hand is a blackjack btw
                            $_SESSION["casino_blackjack_player_status"] = "push";
                            $_SESSION["casino_blackjack_dealer_status"] = "push";
                            $reimburse = $_SESSION["casino_blackjack_bet"];
                            $stmt_reimburse = $this->db->prepare('UPDATE economy SET pocket_money = pocket_money + ? WHERE id = ?');
                            $stmt_reimburse->execute([$reimburse, $this->user_info['id']]);
                        } else {
                            $_SESSION["casino_blackjack_player_status"] = "won";
                            $reimburse = $_SESSION["casino_blackjack_bet"] * 2.5;
                            $stmt_reimburse = $this->db->prepare('UPDATE economy SET pocket_money = pocket_money + ? WHERE id = ?');
                            $stmt_reimburse->execute([$reimburse, $this->user_info['id']]);
                        }
                    } else if (array_sum($_SESSION["casino_blackjack_deal_hand"]) == 21) {
                        $_SESSION["casino_blackjack_dealer_status"] = "won";
                    }
                    

                    die(json_encode([
                        "player_status" => $_SESSION["casino_blackjack_player_status"], 
                        "dealer_status" => $_SESSION["casino_blackjack_dealer_status"],
                        "money" => $_SESSION["casino_blackjack_bet"]
                    ]));
                }
            }
            exit;
            break;
        case 'draw':
            if (isset($_SESSION["casino_blackjack_player_status"])) {
                if ($_SESSION["casino_blackjack_player_status"] == "playing") {
                    $your_card = draw_card($_SESSION["casino_blackjack_your_hand"]);
                    $_SESSION["casino_blackjack_your_hand"][] = $your_card;

                    if (array_sum($_SESSION["casino_blackjack_your_hand"]) == 21) {
                        $_SESSION["casino_blackjack_dealer_status"] = "lost";
                        $_SESSION["casino_blackjack_player_status"] = "won";
                    }

                    if (array_sum($_SESSION["casino_blackjack_your_hand"]) > 21) {
                        $_SESSION["casino_blackjack_dealer_status"] = "won";
                        $_SESSION["casino_blackjack_player_status"] = "lost";
                    }
                }
                
                if ($_SESSION["casino_blackjack_player_status"] == "won") {
                    $reimburse = $_SESSION["casino_blackjack_bet"] * 2;
                    $stmt_reimburse = $this->db->prepare('UPDATE economy SET pocket_money = pocket_money + ? WHERE id = ?');
                    $stmt_reimburse->execute([$reimburse, $this->user_info['id']]);
                }
                
                die(
                    json_encode(
                        [
                            [$your_card, array_sum($_SESSION["casino_blackjack_your_hand"])],
                            ["?", array_sum($_SESSION["casino_blackjack_deal_hand"])],
                            [$_SESSION["casino_blackjack_player_status"], $_SESSION["casino_blackjack_dealer_status"]]
                        ]
                    )
                );
            }
            break;
        case 'stand':
            if (!isset($_SESSION["casino_blackjack_player_status"])) break;
            
            $_SESSION["casino_blackjack_player_status"] = "stand";

            while (array_sum($_SESSION["casino_blackjack_deal_hand"]) < 17) {
                $their_card = draw_card($_SESSION["casino_blackjack_deal_hand"]);
                $_SESSION["casino_blackjack_deal_hand"][] = $their_card;
            }

            $dealer_score = array_sum($_SESSION["casino_blackjack_deal_hand"]);
            $player_score = array_sum($_SESSION["casino_blackjack_your_hand"]);

            if ($dealer_score > 21) {
                $_SESSION["casino_blackjack_player_status"] = "won";
                $_SESSION["casino_blackjack_dealer_status"] = "lost";
            } elseif ($dealer_score > $player_score) {
                $_SESSION["casino_blackjack_player_status"] = "lost";
                $_SESSION["casino_blackjack_dealer_status"] = "won";
            } elseif ($player_score > $dealer_score) {
                $_SESSION["casino_blackjack_player_status"] = "won";
                $_SESSION["casino_blackjack_dealer_status"] = "lost";
            } else {
                $_SESSION["casino_blackjack_player_status"] = "push";
                $_SESSION["casino_blackjack_dealer_status"] = "push";
            }

            if ($_SESSION["casino_blackjack_player_status"] == "won") {
                $reimburse = $_SESSION["casino_blackjack_bet"] * 2;
            } elseif ($_SESSION["casino_blackjack_player_status"] == "push") {
                $reimburse = $_SESSION["casino_blackjack_bet"] * 1;
            } else {
                $reimburse = 0;
            }
            
            if ($reimburse > 0) {
                $stmt_reimburse = $this->db->prepare('UPDATE economy SET pocket_money = pocket_money + ? WHERE id = ?');
                $stmt_reimburse->execute([$reimburse, $this->user_info['id']]);
            }

            die(
                json_encode(
                    [
                        [null, array_sum($_SESSION["casino_blackjack_your_hand"])],
                        [$_SESSION["casino_blackjack_deal_hand"], array_sum($_SESSION["casino_blackjack_deal_hand"])],
                        [$_SESSION["casino_blackjack_player_status"], $_SESSION["casino_blackjack_dealer_status"]]
                    ]
                )
            );

            break;
        case 'forfeit':
            if (isset($_SESSION["casino_blackjack_player_status"])) {
                $_SESSION["casino_blackjack_bet"] = null;
                $_SESSION["casino_blackjack_deck"] = null;
                $_SESSION["casino_blackjack_your_hand"] = null;
                $_SESSION["casino_blackjack_deal_hand"] = null;
                $_SESSION["casino_blackjack_player_status"] = null;
                $_SESSION["casino_blackjack_dealer_status"] = null;
            }
            die(json_encode([])); // wii need to return json
            break;
        default:
            break;
    }
}

switch ($args) {
    case "status":
        die(json_encode([
            "player_status" => $_SESSION["casino_blackjack_player_status"], 
            "dealer_status" => "playing",
            "money" => $_SESSION["casino_blackjack_bet"]
        ]));
        break;
    case "cards":
        $cards = $_SESSION["casino_blackjack_deal_hand"];
        $upper_card = array_shift($cards);
        $obsfuscated_hand = array_fill(0, count($cards), "?");
        $dealer_hand = array_merge([$upper_card], $obsfuscated_hand);
        $dealer_card_sum = 0;

        if (array_sum($_SESSION["casino_blackjack_your_hand"]) == 21) {
            $_SESSION["casino_blackjack_player_status"] = "won";
            $_SESSION["casino_blackjack_dealer_status"] = "lost";
        }

        if (array_sum($_SESSION["casino_blackjack_deal_hand"]) == 21) {
            $_SESSION["casino_blackjack_dealer_status"] = "won";
            $_SESSION["casino_blackjack_player_status"] = "lost";
        }

        if ($_SESSION["casino_blackjack_player_status"] == "lost" || $_SESSION["casino_blackjack_player_status"] == "won") {
            $dealer_hand = $cards;
            $dealer_card_sum = array_sum($_SESSION["casino_blackjack_deal_hand"]);
        }
        die(
            json_encode(
                [
                    [$_SESSION["casino_blackjack_your_hand"], array_sum($_SESSION["casino_blackjack_your_hand"])],
                    [$dealer_hand, array_sum($_SESSION["casino_blackjack_deal_hand"])],
                    [$_SESSION["casino_blackjack_player_status"], $_SESSION["casino_blackjack_dealer_status"]]
                ]
            )
        );
        break;
}

?>
<audio id="song" loop src="https://lsdblox.cc/asset/?id=116&force"></audio>
<div class="fc hidden" id="do_you_like_my_drawing">
    <button id="forfeit" class="background-color:var(--evil)">Restart</button>
    <span id="money_on_the_line">¥???</span>
    <a href="#" id="unmute"></a>
    <img src="https://lsdblox.cc/asset/?id=115">
    <div id="dealer_hand" class="fr"></div>
    <div class="fr" id="actions">
        <button id="stand">Stand</button>
        <button id="draw">Draw</button>
    </div>
</div>
<div class="fc" id="begin_game">
    <span>
        ¥<span id="amount_of_pocket_money"><?=$this->economy["pocket_money"]?></span> 
        in pocket
    </span>
    <span id="start_status_span">Place your bet</span>
    <div class="fr" id="begin_game">
        <input type="number" placeholder="0" value="0" id="amount" min="0">
        <button id="init">Begin</button>
    </div>
</div>
<div id="your_hand" class="fr"></div>
<script src="/assets/js/blackjack.refactor.js">
</script>