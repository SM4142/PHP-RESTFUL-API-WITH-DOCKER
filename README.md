Step by step how to work

--Route
working on get,post,put and delete
you only need to use Route class it works on Route
Route class works with RoutingFunction.
It works like we get sever_url and method and we compare this value with our new Route value one by one.
if you don't any params it ends here the function redirect you to the page.
but if you want to use params there is two way if it is necessary then just write like that /{id}if doesn't necessary use like that /{!name}.
how should you use the Route ;
if you want the use neccessary params and unneccessary params together use like first necessary params /movies/{id}/{!name} .
You can use a controller to hold function or you can use just fuction.
if you set params you have just get a value in function it gives you params as array.
--Response
This is another class we'll ussually use to return something.
if you want to return json data you should use Response::sendResponse function.
how to use you can use like that
