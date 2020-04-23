# Laravel Video Chat
Laravel Video Chat using Openvidu 

# АПИ v1

## Авторизация

###Зарегистрировать пользователя

POST https:://app.radius-etl.ru/api/auth/register 

Образец запроса: 
```
{
	"first_name": "Иван",
	"last_name": "Иванов",
	"email": "user@example.com",
	"password": "user",
	"password_confirmation": "user",
}
```
Образец ответа: 
```
{
	"success": true,
	"message": "Вы успешно зарегистрировались"
}
```

### Аутентификация пользователя 

POST https:://app.radius-etl.ru/api/auth/login 

Образец запроса: 
```
{
	"email": "user@example.com",
	"password": "user",
}
```
Образец ответа: 
```
{
	"success": true,
	"message": "Вы успешно вошли в систему",
	"token": "ab1.cd2.ef3",
	"two_factor_code": false,
	"reload": false,
	"user": {
		"id": 2,
		"email": "user@example.com",
		"password": "user",
		"profile": {
			"first_name": "Иван",
			"last_name": "Иванов",
		},
		"roles":[
			{
				"id": 2,
				"name": "Пользователь",
				"slug": "user",
				"permissions": [
					{
						"name": "enable-login",
						"status": true
					}
				]
			}
		],
		"permissions": []
	}
}
```

## Беседы 

Требуется авторизация через Bearer token. Его можно получить через запрос аутентификации пользователя. 

### Просмотреть список 

GET https:://app.radius-etl.ru/api/chat/conversations 

Образец ответа: 
```
{
	"success": true,
	"conversations": [
		{
			"id": 1,
			"name": "ConversationName",
			"users": [
				{
					"id": 2,
					"email": "user@example.com",
					"password": "user",
					"profile": {
						"first_name": "Иван",
						"last_name": "Иванов",
					},
					"roles":[
						{
							"id": 2,
							"name": "Пользователь",
							"slug": "user",
							"permissions": [
								{
									"name": "enable-login",
									"status": true
								}
							]
						}
					],
					"permissions": []
				}
			],
			"messages": [],
			"files": []
		}
	]
}
```

### Создать беседу 

POST https:://app.radius-etl.ru/api/chat/conversations 

Образец запроса: 
```
{
	"name": "Conversation2"	
}
```

Образец ответа: 
```
{
	"success": true,
	"conversationId": 2	
}
```

### Удалить беседу (если там есть только вы) 

DELETE https:://app.radius-etl.ru/api/chat/conversations/{conversation}

Образец ответа: 
```
{
	"success": true,
	"conversation": 2	
}
```

## Участники 

### Просмотреть список

GET https:://app.radius-etl.ru/api/chat/conversations/{conversation}/participants 

Образец ответа: 
```
{
	"success": true,
	"participants": [
		{
			"id": 2,
			"email": "user@example.com",
			"password": "user",
			"profile": {
				"first_name": "Иван",
				"last_name": "Иванов",
			},
			"roles":[
				{
					"id": 2,
					"name": "Пользователь",
					"slug": "user",
					"permissions": [
						{
							"name": "enable-login",
							"status": true
						}
					]
				}
			],
			"permissions": []
		}
	]
}
```

### Добавить  

POST https:://app.radius-etl.ru/api/chat/conversations/{conversation}/participants 

Образец запроса: 
```
{
	"users": [
		3
	]
}
```

Образец ответа: 
```
{
	"success": true
}
```

### Удалить  

DELETE https:://app.radius-etl.ru/api/chat/conversations/{conversation}/participants/{participant} 

Образец ответа: 
```
{
	"success": true
}
```

## Файлы 

### Список  

GET https:://app.radius-etl.ru/api/chat/conversations/{conversation}/files 

Образец ответа: 
```
{
	"success": true,
	"files": [
		{
			"id": 1,
			"conversation_id": 2,
			"message_id": 1,
			"user_id": 2,
			"name": "20200422161508-php.log",
			"file_details": {
				"fullPath":"/20200422161508-php.log"
				"mimeType":"text/plain"
				"name":"20200422161508-php.log"
				"size":134381
				"webPath":"https://www.radius-micro.me/storage/20200422161508-php.log"
			}
		}
	]
}
```

### Добавить  

POST https:://app.radius-etl.ru/api/chat/conversations/{conversation}/files 

Образец запроса: 
```
{
	"files": [
		<binary>
	]
}
```

Образец ответа: 
```
{
	"success" : true,
    "message" : "Файлы отправлены",
    "files" : [
    	{
			"id": 2,
			"conversation_id": 2,
			"message_id": 0,
			"user_id": 2,
			"name": "20200422161508-java.log",
			"file_details": {
				"fullPath":"/20200422161508-java.log"
				"mimeType":"text/plain"
				"name":"20200422161508-java.log"
				"size":134381
				"webPath":"https://www.radius-micro.me/storage/20200422161508-java.log"
			}
		}
    ]
}
```

### Удалить  

DELETE https:://app.radius-etl.ru/api/chat/conversations/{conversation}/files/{file} 

Образец ответа: 
```
{
	"success" : true,
    "message" : "Файл удалён"
}
```

## Сообщения 

### Список  

GET https:://app.radius-etl.ru/api/chat/conversations/{conversation}/messages 

Образец ответа: 
```
{
	"success": true,
	"messages": [
		{
			"id": 1,
			"conversation_id": 2,
			"user_id": 2,
			"text": "text",
			"files": [],
			"sender": {
				{
					"id": 2,
					"email": "user@example.com",
					"password": "user",
					"profile": {
						"first_name": "Иван",
						"last_name": "Иванов",
					},
					"roles":[
						{
							"id": 2,
							"name": "Пользователь",
							"slug": "user",
							"permissions": [
								{
									"name": "enable-login",
									"status": true
								}
							]
						}
					],
					"permissions": []
				}
			}
		}
	]
}
```

### Добавить  

POST https:://app.radius-etl.ru/api/chat/conversations/{conversation}/messages 

Образец запроса: 
```
{
	"text": "text",
	"files": [1]
}
```

Образец ответа: 
```
{
	"success" : true,
    "message" : "Сообщение отправлено"
}
```

### Удалить  

DELETE https:://app.radius-etl.ru/api/chat/conversations/{conversation}/messages/{message} 

Образец ответа: 
```
{
	"success" : true,
    "message" : "Сообщение удалено"
}
```