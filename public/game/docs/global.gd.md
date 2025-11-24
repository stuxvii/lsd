`global.gd` contains all the essential gameplay variables.

<br>
`sensitivity`: Camera sensitivity

<br>
`volume`: Mostly unused

<br>
`map_list`: Dictionary with data for levels.
<br>
```
var map_list = {
    # World (Map Pack)
    0: {
        # Map (Level)
        0: {
            # Path relative to "res://scenes/".
            # This would resolve to "res://scenes/map_pack_1/map_1.tscn".
            "location": "map_pack_1/map_1.tscn",

            # The following resolve both in the In-Game UI 
            # and in the Map Selection UI in the Main Menu
            "name": "First Map for Map Pack 1",

            # Format has to be "Artist - Song Name" with proper capitalization
            "song_name": "Rick Astley - Never Gonna Give You Up"

            # Path relative to "res://audio/".
            # This would resolve to "res://audio/music/never_gonna_give_you_up.ogg".
            "theme": "music/never_gonna_give_you_up.ogg" # Preferably in Ogg Vorbis format
        }
    }
}
```

<br>
<br>

Used by the Player and WinFlag to manage Map data and either 

display it or do math with it (as to calculate next map).

`current_map`: Integer, resolves to Map inside of map_list.

`current_world`: Integer, resolves to World inside of map_list.

`current_song`: String, used for the Sound Trigger's Persist option.
<br>
<br>
`performance`: Boolean, When this is true the 3D resolution is set to 30% and particles are reduced.

`shutup`: Boolean, Prevents my voicelines from playing when the Player wins or dies.

`music_pitch`: Float, Changes the `pitch_scale` of the currently playing song.

`run_time`: Float, increments by `1.0` every second. Shown on the bottom center. May not be fully accurate.
<br>

`music_time`: Float, is set to the current position of the currently playing song. 
<br>
Used to resume playback at the correct position when soft resetting.

`run_dnf`: Boolean, set to `true` if the Player uses a skip, set to `false` when the run is restarted

`health`: Integer, ranges from 10 to -10. If this is less than, -1 the Player is considered
<br>
to be dead.

`checkpoint`: Vector3, resolves to the position of the last claimed checkpoint.
<br>
The Player will appear here if they soft reset, and Vector3 is not `Vector3.ZERO`.
<br>
This value is set to `Vector3.ZERO` on the start of the game and if the player touches a WinFlag

`camerapos`: Unused, to be deprecated

`cutscene_skipped`: Starts as false, when the intro cutscene ends this is set to true, making it
<br>
so the cutscene doesn't appear when the Main Menu is opened.
