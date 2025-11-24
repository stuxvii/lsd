This page expects you to have some minimal knowledge of how to use a computer
<br>
<br>

## Preparation
<br>

1. Find the following TSCN file in the project's File System
<br>
<br>

![Godot File System](/game/docs/image.png)

<br>
2. Right click on it and duplicate it

![Right click Context Menu](/game/docs/image-1.png)
<br>
<br>

3. Give it a name. You can use slashes to place it inside of a folder 
(or create a new folder with your map as its content)
<br>
<br>

![alt text](/game/docs/image-2.png)
<br>
<br>

4. Find the new TSCN file in the File System and open it by double clicking it
<br>
![alt text](/game/docs/image-3.png)
<br>
<br>
#### You should now see this:
<br>
![alt text](/game/docs/image-4.png)
<br>
<br>
## Level
the Level node is the main container for the Map's Layout nodes.
<br>
![alt text](/game/docs/image-5.png)
<br>
(Tip: You may delete any Layout nodes that you are not going to use)
<br>
<br>
### Terrain
The Terrain node contains GridMap nodes.
<br>
![alt text](/game/docs/image-10.png)
<br>
The Main GridMap node allows for collision with PhysicsBody3D nodes with collision layer 1 like the Player or Ball
<br>
The Ball GridMap node allows for collision with PhysicsBody3D nodes with collision layer 2 like the Ball
<br>
<br>
#### How to GridMap [(Main Article)](https://docs.godotengine.org/en/stable/tutorials/3d/using_gridmaps.html)
<br>
Click on any of the GridMap nodes, you will see this menu pop up on the bottom:
<br>
![alt text](/game/docs/image-7.png)
<br>
#### Main GridMap Tools
(In order of appearance)
<br>
Transform: Allows you to move the entire GridMap node at once, including all blocks placed down inside of it
<br>
Selection: Allows you to select an area in the current GridMap Floor, to either use the Move, Copy, Duplicate, Fill, or Delete tools on it.
<br>
Erase: Allows you to manually erase blocks from the current GridMap Floor
<br>
Paint: Allows you to manually place blocks in the current GridMap Floor
<br>
(Fill, Move, Copy, Duplicate, Rotate X, Rotate Y, Rotate Z are all self explanatory)
<br>
GridMap Floor: Allows you to change the height of the blocks you place
<br>
<br>
### Scripted Objects
You're provided with various objects that contain scripts, hence they're called "Scripted Objects".
<br>
These can be multiple things like a Spring that shoots any PhysicsBody3D nodes up, a platform that moves,
<br>
a Spike that harms the player, a Checkpoint that saves your location inside of global.gd, and more.
<br>
<br>
**All Scripted Objects are able to be found inside of `res://objects/world/`.**
<br>
<br>
These do not explicitly require any organization, but you should preferably organize them inside Node3D containers.
<br>
To insert them in the Map, simply drag and drop their TSCN file into the scene or right click their TSCN file and select "Instantiate".
<br>
<br>
#### List of Scripted Objects
[Ball](https://lsdblox.cc/tuxgolf/documentation?page=Ball.md)
<br>
[Bullet](https://lsdblox.cc/tuxgolf/documentation?page=Bullet.md)
<br>
[Button](https://lsdblox.cc/tuxgolf/documentation?page=Button.md)
<br>
[Checkpoint](https://lsdblox.cc/tuxgolf/documentation?page=Checkpoint.md)
<br>
[Machine Gun](https://lsdblox.cc/tuxgolf/documentation?page=Machine%20Gun.md)
<br>
[Moving Platform](https://lsdblox.cc/tuxgolf/documentation?page=Moving%20Platform.md)
<br>
[Sound Trigger](https://lsdblox.cc/tuxgolf/documentation?page=Sound%20Trigger.md)
<br>
[Spike](https://lsdblox.cc/tuxgolf/documentation?page=Spike.md)
<br>
[Spike Wall](https://lsdblox.cc/tuxgolf/documentation?page=Spike%20Wall.md)
<br>
[Spring](https://lsdblox.cc/tuxgolf/documentation?page=Spring.md)
<br>
[WinFlag](https://lsdblox.cc/tuxgolf/documentation?page=WinFlag.md)
