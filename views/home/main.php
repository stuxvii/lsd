<div class="fr" style="overflow:hidden;">
<?=$this->insertAd()?>
<div style="height:75%">
<form id="plrform" method="post" action="/social/feed" class="feed_compose">
    <textarea rows="8" cols="32" type="textarea" id="message" name="message" maxlength="512" required placeholder="I'm eating a VERY yummy sandwich and none of you can have some..."></textarea>
    <input type="submit" value="Send" style="margin-top:15px;">
    <input type="hidden" name="csrftoken" value="<?=$_SESSION["csrftoken"]?>">
</form>
<div class="aifs border" style="justify-content: start;height:70%" id="messagedrawer"></div>
</div>
<?=$this->insertAd()?>
</div>
<script src="/assets/js/feed.js"></script>