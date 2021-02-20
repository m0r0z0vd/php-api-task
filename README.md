# Secure Information Storage REST API

### Project setup

* Add `secure-storage.localhost` to your `/etc/hosts`: `127.0.0.1 secure-storage.localhost`

* Run `make init` to initialize project

* Open in browser: http://secure-storage.localhost:8000/item Should get `Full authentication is required to access this resource.` error, because first you need to make `login` call (see `postman_collection.json` or `SecurityController` for more info).

### Run tests

make tests

### API credentials

* User: john
* Password: maxsecure

### Postman requests collection

You can import all available API calls to Postman using `postman_collection.json` file

### Available endpoints

#### Login
* URI: `/login`
* method: `POST`
* body: `raw JSON`
* example: 
```
  {
      "username": "john",
      "password": "maxsecure"
  }
```

#### Logout
* URI: `/logout`
* method: `POST`
* body: `none`

#### Get items
* URI: `/item`
* method: `GET`
* body: `none`

#### Create item
* URI: `/item`
* method: `POST`
* body: `form data`
* example: 
```
  name="data" value="new item secret"
```

#### Update item
* URI: `/item`
* method: `PUT`
* body: `form data`
* example: 
```
  name="id" value="1"
```
```
  name="data" value="new secret"
```

#### Delete item
* URI: `/item/{id}`
* method: `DELETE`
* body: `none`
