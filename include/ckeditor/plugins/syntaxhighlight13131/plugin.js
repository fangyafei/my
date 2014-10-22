CKEDITOR.plugins.add("syntaxhighlight", {  
     requires : [ "dialog" ],  
     lang : [ "cn" ],  
     init : function(a) {  
         var b = "syntaxhighlight";  
         var c = a.addCommand(b, new CKEDITOR.dialogCommand(b));  
         c.modes = {  
             wysiwyg : 1,  
             source : 1  
         };  
         c.canUndo = false;  
         a.ui.addButton("Code", {  
             label : a.lang.syntaxhighlight.title,  
             command : b,  
             icon : this.path + "images/syntaxhighlight.gif" 
         });  
         CKEDITOR.dialog.add(b, this.path + "dialogs/syntaxhighlight.js")  
     }  
 }); 