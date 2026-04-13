# API VWAJEN v1

Base URL locale: `http://127.0.0.1:9000/api/v1`

## Authentification

- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/logout` (Bearer token)
- `GET /auth/me` (Bearer token)

## Pôle Éducation

- `GET /courses`
- `GET /courses/{id}`
- `GET /courses/{courseId}/lessons`
- `POST /courses/{id}/enroll` (auth)
- `POST /courses/{courseId}/lessons/{lessonId}/complete` (auth)
- `GET /my/courses` (auth)

## Pôle Contenu / Feed

- `GET /feed`
- `GET /feed/following` (auth)
- `GET /feed/videos/{id}`
- `POST /feed/videos/{id}/like` (auth)
- `POST /feed/videos/{id}/comment` (auth)
- `GET /feed/videos/{id}/comments`
- `GET /feed/lives`
- `GET /feed/lives/scheduled`

## Live & Mobilisation

- `POST /lives` (auth)
- `POST /lives/{id}/start` (auth)
- `POST /lives/{id}/end` (auth)
- `POST /lives/{id}/join` (auth)
- `POST /lives/{id}/leave` (auth)

## Evénements

- `GET /events`
- `GET /events/{id}`
- `GET /events-calendar`
- `POST /events/{id}/participate` (auth)
- `DELETE /events/{id}/participation` (auth)
- `GET /my/events` (auth)

## Actions citoyennes

- `GET /actions`
- `GET /actions/map`
- `GET /actions/{id}`
- `POST /actions` (auth)
- `POST /actions/{id}/join` (auth)
- `DELETE /actions/{id}/join` (auth)

## Adhésion, bibliothèque, communication, modération

- `POST /memberships` (auth)
- `GET /memberships/me` (auth)
- `GET /library` (auth)
- `POST /library/save` (auth)
- `DELETE /library/save` (auth)
- `GET /communication/ads`
- `POST /moderation/reports` (auth)
- `GET /moderation/reports/mine` (auth)
