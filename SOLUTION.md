# Solution

## Structure

- `app/lib` (`Lib\` namespace): framework code (router, request, response, base controllers, http exception)
- `app/src` (`App\` namespace): business code (contact entity, validator, file repository, controllers)
- `app/var/contacts`: stored contact forms, one json file per form (ignored by git)

## Endpoints

Every endpoint answers with `Content-Type: application/json`. Requests with a body must send
`Content-Type: application/json` (otherwise `415`). If an `Accept` header is sent, it must accept json (otherwise `406`).

| Method   | Uri                    | Success                      | Errors                                    |
|----------|------------------------|------------------------------|-------------------------------------------|
| `POST`   | `/contact`             | `201` `{"file": "<filename>"}` | `400` invalid body, `409` duplicate file  |
| `GET`    | `/contact`             | `200` list of contact forms  |                                           |
| `GET`    | `/contact/<filename>`  | `200` the contact form       | `404`                                     |
| `PATCH`  | `/contact/<filename>`  | `200` the updated form       | `400` invalid body, `404`                 |
| `DELETE` | `/contact/<filename>`  | `204` no content             | `404`                                     |

Errors look like `{"error": "invalid request body", "details": ["the field \"recipient\" is not allowed"]}`.

## Choices

- Files are named `<timestamp>_<email>.json` (ex: `1731245562_leia@alderaan.com.json`), as asked for the storage.
  The `POST` response returns this same name so it can be used directly in `/contact/<filename>`.
- The file name is the identifier of a contact form: it does not change when the email is updated with `PATCH`.
- Validation: `email` must be a valid email address (max 254 chars), `subject` (max 255) and `message` (max 5000)
  must be non-empty strings. Values are trimmed. Stored files are validated too before being sent back.
- The file name from the uri is checked against the expected pattern to prevent path traversal.

## Quick test

```bash
curl -X POST 127.0.0.1:8080/contact -H 'Content-Type: application/json' \
  -d '{"email":"leia@alderaan.com","subject":"Help me Obi Wan","message":"You are my only hope"}'
curl 127.0.0.1:8080/contact
curl 127.0.0.1:8080/contact/<filename>
curl -X PATCH 127.0.0.1:8080/contact/<filename> -H 'Content-Type: application/json' -d '{"subject":"new subject"}'
curl -X DELETE 127.0.0.1:8080/contact/<filename>
```

On a Linux host, if the contact forms cannot be saved (500), give Apache the right to write in `var`:

```bash
docker compose exec php-framework-php chown -R www-data:www-data var
```
