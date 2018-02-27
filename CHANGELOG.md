BETA
================

Updates / Changes
----------------

### Beta 0.8.1

Using a config.php file in root, it is now possible to run this project on a server much easier. Just `git clone [this]` and keep doing `git pull origin master` when you need the latest updates. With your custom `config.php` you're sure to always be rolling.

### Beta 0.8

Finally we have sorting of pages (only parents). This is a Step 7 functionality. You hit a button and can then drag and drop to reorder the pages. When pressing the "save" button, an ajax call will store the pages in the background and then redirect you back. This sort order is respected in both step 3 and 7 when listing pages. This is yet another pretty huge function that makes great improvements to this project. We use RubaXa's Sortable.js to get this type of functionality.

### Beta 0.7.9

Fourth Ajax-function: select parent, and then add other pages as its children. This also happens on Step 3. When first selecting any page (that is not a child) as parent, you reload the page. Now you can click buttons for each page you want to add as child to the selected parent. This is done smoothly with ajax. You can undo your action too. But you cannot promote existing children into parents ... yet.

### Beta 0.7

Third Ajax-function: delete. On Step 3 you can now in a very simple and fast way delete pages. You can also undo this as we flag the delete in the database and are not really deleting the data. Also made huge UX improvements to the Split-function. And a lot of other minor adjustments, improvements, and bug fixes. Like better sorting of page lists.

### Beta 0.6

Second Ajax-function introduced (we sure need more of those), this time on Step 3. When you Manage your page structure it's now possible to update Title and/or the slug of a page by just changing its values in the fields directly in the table. When you leave the field the change is automatically stored. No save buttons! Ultra-smooth. Some more tweaking on this will come during the beta, ofc.

### Beta 0.5

It is now possible to go from Step 1 all the way to a fully functional XML output. However, still loads and loads of fixes, improvements, refactorings, and bugs to be fixed. The XML you get now is just a printed string, so some manual labor is needed. Among a lot of other bits and pieces to sort out. Stay tuned for more!

### Beta 0.2

Full work in progress! Removed most cookie-code and WordPress DB-code I could find. Also rewrote Step 7 into "Finalize" and got it to list pages, delete pages, calculate a Title for each page (also copied to Step 3), and open a modal window for HTML edit. The Save here doesn't work yet though. You can now also create brand new pages in this step!

Also re-designed the Project Manager and Project Select pages giving the project lists big buttons instead of small links.

More to come. Next big thing is also of course to get the new Step 8 - "Export" - to work.

### Beta 0.0.1

Start of Life. Major re-structuring and changes to the fundamentals of [Migrate 2 WordPress](https://github.com/Bellfalasch/Migrate-2-WP). Created this new repo for it to work on until we leave the Beta stage at "Beta 1.0" (will become the 2.0 version of this project). Stay tuned!
