<html>
    <head>
        <link rel="stylesheet" href="/game/game.css">
        <link rel="icon" href="/game/icon.png">
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
    </head>
    <body class="fr">
        <div class="fc">
            <div class="fc game">
                <div id="div">
                    <div class="windows_xp_titlebar hidden" id="win_xp_tb">
                        <div class="windows_xp_name_icon">
                            <div class="windows_xp_icon"></div>
                            <span class="windows_xp_name">TuxGolf</span>
                        </div>
                        <div class="windows_xp_close_btn" id="close">X</div>
                    </div>
                </div>
            </div>
            <div class="even">
                <button onclick="init();" id="button">play</button>
            </div>
            <br>
            <div id="warning" class="fc">
                Safari users may have some issues playing.
                <span style="background-color:darkred;">Pressing play will auto-play sound. Lower your volume!</span>
                <span>The web version has some issues with audio and load times.</span>
                <span>Consider playing on the standalone version if you can</span>
            </div>
            <div>
                <div class="even">
                    downloads:
                    <a href="/game/tuxgolf-linux.zip" target="_blank">Linux</a>
                    <a href="/game/tuxgolf-linux" target="_blank">Legacy</a>
                    <a href="/game/tuxgolf.apk" target="_blank">Android</a>
                </div>
                <div class="even">
                    <a href="#" onclick="show_info()">readme</a>
                    <a href="#" onclick="show_controls()">controls</a>
                    <a href="#" onclick="show_credits()">credits</a>
                    <a href="/tuxgolf/documentation">documentation</a>
                </div>
                <div class="even">Meows: <?=$hits?></div>
            </div>
            <div id="info" class="hidden focus">
                <div class="readme">
                    <h4>What are you trying to prove with this game?</h4>
                    <span>That penguins are cool and pushing 🅿️ (rock) is cool.</span>
                    <br>
                    <h4>What is the difference between the web and the standalone versions?</h4>
                    <span>They will run better, have widescreen support and fullscreen support.</span>
                    <span>also the web version has some timing issues with the audio.</span>
                    <br>
                    <h4>Why is the game so heavy (80mbs&gt;) but looks so bad?</h4>
                    <span>The main reason behind this is the audio files.</span>
                    <span>I decided that if you're not gonna be having nice graphics,</span>
                    <span>you should atleast be having high quality tracks, you get me?</span>
                    <br>
                    <h4>Will there be a Windows release of this game?</h4>
                    <span>No, the character is a penguin, not a window or glass panel.</span>
                    <br>
                    <h4>... but there's an Android release..?</h4>
                    <span>Well, what if I told you that the penguin is a robot that looks like a penguin?</span>
                    <span>Which is why he moves like a robot, doesn't blink, has no blood, or stamina.</span>
                    <br>
                    <h4>Wait what about the macOS version?</h4>
                    <span>Well, even if the penguin is an android, the penguin is still a penguin at its core.</span>
                    <span>You know what else has a core? Apples.</span>
                    <br>
                    <span>If you encounter any crashes, please report them to me<br> via a discord dm and include as much detail as possible</span>
                    <a href="#" onclick="hide_info()">okthxbai</a>
                </div>
            </div>
            <div id="credits" class="hidden focus">
                <div class="readme">
                    <span>Huge thanks to the Godot Foundation for the <a href="https://godotengine.org/">Godot Engine</a>, <br>the game engine I used, and having it be FLOSS (free libre open source software)</span>
                    <span>Main developer: acidbox/stuxvii</span>
                    <a href="https://es.wikipedia.org/wiki/Archivo:Industria_argentina.png">"INDUSTRIA ARGENTINA" seal licensed under CC-BY-SA 4.0</a>
                    <span>----------Music----------</span>
                    <br>
                    <span>PS Vita Home Music</span>
                    <span>-Sony (Artist unknown)</span>
                    
                    <br>
                    <span>LONELY ROLLING STAR</span>
                    <span>-Yoshihito Yano</span>
                    <span>-Saki Kabata</span>

                    <br>
                    <span>Better Off Alone</span>
                    <span>-Alice Deejay</span>
                    <span>*Remix by Glejs</span>

                    <br>
                    <span>growing on me</span>
                    <span>-Snayk</span>

                    <br>
                    <span>Aleph-0</span>
                    <span>-LeaF</span>
                
                    <br>
                    <span>magic 7/11</span>
                    <span>-Lexycat</span>
                    
                    <br>
                    <span>Fine Night</span>
                    <span>-goreshit</span>
                    
                    <br>
                    <span>Trivium</span>
                    <span>-C Y G N</span>
                    
                    <br>
                    <span>Bangarang</span>
                    <span>-Skrillex</span>
                    
                    <br>
                    <span>Aryx</span>
                    <span>-Karsten Koch</span>
                    
                    <br>
                    <span>Casin</span>
                    <span>-Glue70</span>
                    <br>
                    <span>I loooove siIvagunner</span>
                    <a href="#" onclick="hide_credits()">okthxbai</a>
                </div>
            </div>
            <div id="controls" class="hidden focus">
                <div class="readme">
                    <span>Keyboard</span>
                    <span>ADSW: Strafe</span>
                    <span>Mouse: Move the camera</span>
                    <span>Space: Jump</span>
                    <span>Shift: Sprint</span>
                    <span>Tab: Settings</span>
                    <span>R: Reload level</span>
                    <span>B: Debug info</span>
                    <a href="#" onclick="hide_controls()">okthxbai</a>
                </div>
            </div>
        </div>
        <script src="/game/game.js">
        </script>
    </body>
</html>
