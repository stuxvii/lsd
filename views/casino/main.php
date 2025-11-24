
<div class="fc">
    <span><?=$msg?></span>
    <h2>Spending balance: ¥<?=$this->economy["money"]?></h2>
    <div class="fr">
        <div class="border aifs">
            <form method="post" action="/casino" class="fc aifs">
                <span>Deposit money</span>
                <input type="number" name="amount" id="deposit_amount" placeholder="10">
                <input type="hidden" name="csrftoken" value="<?=$_SESSION["csrftoken"]?>">
                <input type="submit" value="Deposit">
            </form>
        </div>
        <h3 id="indicator">■</h3>
    </div>
    <h2>Gambling balance: ¥<?=$this->economy["pocket_money"]?>&nbsp;-&nbsp;<a href="#" onclick="alert('The separation between \'gambling\' and \'spending\' balance is to make sure you don\'t overspend all your money. Recommendation: Deposit the amount you want to use into your gambling account and stick with that, and when you\'re done, send it back to your spending account.')">?</a></h2>
</div>
<div>
    <a href="https://www.google.com/search?q=gambling%20addiction%20resources" class="border" style="background-color:var(--evil)">Gambling can quickly become an addiction. Click here to find resources to help you recover from an addiction.</a>
    <span class="border" style="background-color:var(--evil)">This virtual casino only and will ever only allow the use of worthless virtual currency. This is only for entertainment.</span>
</div>
<h3>Games</h3>
<a href="/casino/game/roulette">Life or Bath (roulette)</a>
<a href="/casino/game/blackjack">Blackjack</a>
<script>
const ind = document.getElementById("indicator");
document.getElementById("deposit_amount").addEventListener("input", function (event) {
    if (this.value > 0) {
        ind.textContent = "↑";
    } else if (this.value == 0) {
        ind.textContent = "■";
    } else {
        ind.textContent = "↓";
    }
})
document.getElementById("deposit_amount").addEventListener("change", function (event) {
    if (this.value > 0) {
        ind.textContent = "↑";
    } else if (this.value == 0) {
        ind.textContent = "■";
    } else {
        ind.textContent = "↓";
    }
})
</script>