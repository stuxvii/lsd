<?php
$get_ad_stmt = $this->db->prepare("SELECT id, `name` FROM items WHERE type = 10 AND uploadts >= ? ORDER BY RAND() LIMIT 1");
$time_limit = time() - 604800; // dont want ads submitted a week ago to show upppp
$get_ad_stmt->execute([$time_limit]);

$ad_help = $get_ad_stmt->fetch();
if (!$ad_help) {
    $ad["image"] = "/assets/images/no_ads.png";
} else {
    $ad["image"] = "/asset/?id=" . $ad_help["id"];
}
$ad["link"] = $ad_help["name"] ?? "/account/config";
?>
<!DOCTYPE html>
<html>
    <head>
        <style>
            
        html {
            height: 300px;
            width: 300px;
        }
        * {
            margin: 0;
            padding: 0;
            image-rendering: pixelated;
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
        }
        .link {
            height: 300px;
            display:block;
            width: 300px;
            background-color: #a00;
            cursor:alias;
        }
        img {
            max-height: 300px;
            max-width: 300px;
            display: block;
        }
        .border {
            position: absolute;
            height: 300px;
            width: 300px;
            top: 0;
            box-shadow: inset 0 0 0 2px #a00;
            pointer-events: none;
        }
        .help_btn {
            position: absolute;
            top:2px;
            width: 18px;
            height: 21px;
            left:2px;
            background-color: #bfbfbf;
            border-radius: 0px 0px 8px 0px;
            cursor:pointer;
            user-select: none;
        }

        .help_btn:hover {
            background-color: #fff;
        }

        .info_box {
            display: flex;
            flex-direction: column;
            position: absolute;
            height: 300px;
            width: 300px;
            top: 0;
            box-shadow: inset 0 0 0 2px #a00;
            color: white;
            background-color: #616161ee;
        }
        span {
            margin: 0.5rem;
        }
        p {
            color:white;
            text-shadow: 0px 0px 10px white;
        }
        .hidden {
            display: none;
        }
        #close_help_box {
            margin: 2px;
            width: 18px;
            height: 21px;
            background-color: #a00;
            text-align: center;
            border-radius: 0px 0px 8px 0px;
            cursor:pointer;
            user-select: none;
        }
        .buttons {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
        }
        #YES {
            background-color: #0a0;
            padding: 0.25rem;
            cursor: pointer;
        }
        #NO {
            background-color: #a00;
            padding: 0.25rem;
            cursor: pointer;
        }
        </style>
    </head>
    <body>
        <div class="link" id="link" href="<?=$ad["link"]?>">
            <img src="<?=$ad["image"]?>">
        </div>
        <div id="help_btn" class="help_btn">🛈</div>
        <div class="border"></div>
        <div class="info_box hidden" id="help_box">
            <span id="close_help_box">x</span>
            <span>The following advertisement will redirect you to<p id="help_box_ad_link"></p></span>
            <span>You're seeing this because you've opted in to getting ads served.</span>
            <span>If you wish to disable ads, then disable the according checkbox in your account settings.</span>
        </div>
        <div class="info_box hidden" id="redirect_box">
            <span>The following advertisement will redirect you to<p id="redirect_box_ad_link"></p>.</span>
            <span>This is an external website. LSDBLOX does not necessarily indulge or agree in any way shape or form with its contents.</span>
            <span>Do you wish to go there?</span>
            <div class="buttons">
                <span id="YES">YES</span>
                <span id="NO">NO</span>
            </div>
        </div>
        <script>
            const redirect_box_element = document.getElementById("redirect_box");
            const help_box_element = document.getElementById("help_box");
            const link_element = document.getElementById("link");
            const link_site = link_element.getAttribute("href");
            
            const link_resolve = document.createElement("a");
            link_resolve.href = link_site
            
            const link_hostname = link_resolve.hostname;
            const this_hostname = window.location.hostname;

            const redirect_box_ad_link_element = document.getElementById("redirect_box_ad_link");
            redirect_box_ad_link_element.textContent = link_resolve;
            redirect_box_ad_link_element.href = link_resolve;

            const help_box_ad_link_element = document.getElementById("help_box_ad_link");
            help_box_ad_link_element.textContent = link_resolve;
            help_box_ad_link_element.href = link_resolve;

            const help_btn_element = document.getElementById("help_btn");
            help_btn_element.addEventListener("click", function() {
                help_box_element.classList.toggle("hidden");
            });

            const close_help_box_element = document.getElementById("close_help_box");
            close_help_box_element.addEventListener("click", function() {
                help_box_element.classList.toggle("hidden");
            });

            const YES_element = document.getElementById("YES");
            const NO_element = document.getElementById("NO");

            NO_element.addEventListener("click", function() {
                redirect_box_element.classList.toggle("hidden");
            });

            YES_element.addEventListener("click", function() {
                parent.location = link_resolve;
            });

            link_element.addEventListener("click", function() {
                if (link_hostname !== this_hostname) {
                    redirect_box_element.classList.toggle("hidden");
                } else {
                    parent.location = link_resolve;
                }
            });
        </script>
    </body>
</html>