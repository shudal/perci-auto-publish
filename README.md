# Wordpress发布文章接口插件 

## API接口文档

### 说明

- 请求示例
> POST: www.example.com/wp-json/autopublish/v1/post

- POST参数说明

| 参数                              | 说明                                                                      |
| --------------------------------- | ------------------------------------------------------------------------- |
| ak                                | acccess key, 在后台里面的 设置-自动发布 里配置                            |
| sk                                | secret key,  在后台里面的 设置-自动发布里配置                             |
| author_login                      | 用于指定文章的作者。填写这个作者的登陆名                                  |
| post_date                         | 用于指定文章的时间。如"2019-3-1512:11:11", "2019-3-15 12:11"              |
| post_title                        | 文章标题                                                                  |
| category_slug                     | 分类目录的别名                                                            |
| post_content                      | 文章内容。html格式                                                        |
| post_thumbnail                    | 特色图片的超链接。插件将下载图片并上传媒体库，然后设为文章的特色图片      |

- 返回数据示例
```
成功：
{
    'code': 1,
    'msg' : "OK",
    'data': []
}
失败：
{
    'code': -1,
    'msg' : "invalide_access",
    'data': []
}
```
