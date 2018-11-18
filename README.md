### Detail module information

1. Namespace >> **modules/crud_mod_adv**
2. Zip Archive source >> 
    https://github.com/aalfiann/reSlim-modules-crud_mod_adv/archive/master.zip

### How to Integrate this module into reSlim?

1. Download zip then upload to reSlim server to the **modules/**
2. Extract zip then you will get new folder like **reSlim-modules-crud_mod_adv-master**
3. Rename foldername **reSlim-modules-crud_mod_adv-master** to **crud_mod_adv**
4. Done

### How to Integrate this module into reSlim with Packager?

1. Make AJAX GET request to >>
    http://**{yourdomain.com}**/api/packager/install/zip/safely/**{yourusername}**/**{yourtoken}**/?lang=en&source=**{zip archive source}**&namespace=**{modul namespace}**

### How to integrate this module into database?
This module is require integration to the current database.

1. Make AJAX GET request to >>
    http://**{yourdomain.com}**/api/crud_mod_adv/install/**{yourusername}**/**{yourtoken}**

### Security Tips
After successful integration database, you must remove the **install** and **uninstall** router.  
Just make some edit in the **crudmodadv.router.php** file manually.

### Requirement
- This module is require **reSlim** minimum version **1.16.0**.

### Description
This **CrudModAdv** is based from [CrudMod](https://github.com/aalfiann/reSlim-modules-crud_mod) which is has many improved feature also this module was design for general or dynamic purpose, so you may have to make some modification for spesific requirement or different use.