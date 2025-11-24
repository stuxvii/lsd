The Bullet is a dangerous scripted object that moves "Forward" infinitely.

It moves depending on its rotation.

<br>
It has no PHitbox, but has an LHitbox that will harm a Player.

if a player is inside the LHitbox, it will trigger its `hurt()`

method, which will deduct 1 health from `global.health`, and make it

play an "Ouch!" sound.

<br>
The Bullet will de-spawn after 10 seconds of having been spawned.