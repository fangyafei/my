请按照以下步骤将UEditor加入到dedecms里
1.将ueditor文件夹放到您网站目录下的include文件夹下。(include文件夹里可以看到ckeditor)
2.将ueditor文件夹里的inc_fun_funAdmin.php文件覆盖到include\inc\下
3.修改网站里的data/config.cache.inc.php文件里$cfg_html_editor = 'ueditor'。(data与include同目录)



完成以上3步即可将网站里的所有ckeditor替换为ueditor，当想用ckeditor的时候，重复第三步，将$cfg_html_editor设置为ckeditor即可

当ueditor有更新的时候，可以到官网下载合成后的代码包，替换到editor/js里即可

有问题请到http://ueditor.baidu.com上查看
或联系:qq:49002242
有意向可加QQ群：166914291

