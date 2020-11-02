# instant-podcast-rss
Turn an apache index into a podcast RSS feed

Say you have the following file structure on a server:

````
example.com/podcast/
│   image.png
│
├───show-1
│       a.mp3
│       b.mp3
│       c.mp3
│
├───show-2
│       a.mp3
│       b.mp3
│       c.mp3
│       image.png
│
└───show-3
        a.mp3
        a.mp3.png
        b.mp3
        c.mp3
````

By placing the files in this repo into a single root folder, you create rss feeds for every folder.

`example.com/podcast/` will display a feed for all files in all folders.

`example.com/podcast/show-1/` will display a feed for show-1.

`image.png` in your linked folder will be the main podcast image.

`image.png` in a subfolder will apply to episodes in that subfolder.

`file.mp3.png` will apply to a single file.
