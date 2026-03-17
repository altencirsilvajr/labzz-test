# Guia Rápido para Entrevista Técnica — labzz-test

> Objetivo: te dar um roteiro curto e claro para explicar o projeto, as decisões e os principais desafios.

---

## 1) Elevator pitch (30-60s)

"Eu implementei e estabilizei um chat real-time fullstack com backend em PHP 8.3, frontend em Next.js 15 e bônus mobile em Expo/React Native. Foquei em fluxo real-time, autenticação com Auth0, organização de API/OpenAPI, qualidade com testes e cobertura mínima em CI, além de validação ponta a ponta no mobile via Expo Go."

---

## 2) Arquitetura em 1 minuto

### Backend (PHP monolito)
- Entrypoint HTTP + pipeline de middlewares.
- Módulos principais: Auth, Users, Conversations, Messages, Search, Realtime.
- Persistência e serviços:
  - MySQL (fonte da verdade)
  - Redis (ephemeral/realtime/rate-limit)
  - Elasticsearch (busca)
- OpenAPI disponível em `apps/backend/openapi/openapi.yaml`.

### Frontend (Next.js)
- App Router + TypeScript + React Query.
- i18n (pt-BR/en).
- Proxy server-side para backend.
- Fluxo autenticado com Auth0.

### Mobile (Expo / React Native)
- Lista de conversas, mensagens e envio de mensagens.
- Testado em dispositivo real com Expo Go.
- Configuração de API por `.env` para rodar em rede local.

---

## 3) O que foi entregue (resumo objetivo)

## Backend
- Remoção completa da integração LLM (rotas, handlers, settings, docs).
- Testes unitários reforçados (`CursorTest` + `JsonTest`).
- Ajustes de cobertura e gate mínimo no CI.

## Frontend
- Remoção do fluxo "Ask AI".
- Tipos e testes atualizados pós-remover LLM.
- Cobertura mínima ajustada para 80% nas métricas definidas no Vitest.

## Mobile
- Ajustes para funcionar no Expo Go (token manual + URL de API por rede local).
- Botões de atualização/criação de conversa para facilitar teste funcional.
- Upgrade de compatibilidade de dependências Expo.

## Documentação
- README atualizado com setup web/mobile.
- Swagger/OpenAPI linkado.
- Screenshot de mobile funcionando adicionada.

---

## 4) Decisões técnicas que valem explicar

1. **Remover LLM de ponta a ponta**
   - Motivo: requisito de escopo mudou (não seria usado).
   - Benefício: simplificação de código, menos superfície de erro, menor custo operacional.

2. **Cobertura com gate no CI**
   - Motivo: garantir baseline de qualidade automatizada.
   - Benefício: evita regressão silenciosa em PR/push.

3. **Ajuste de cobertura por escopo realmente testado**
   - Motivo: cobertura global com poucos testes derruba pipeline e não reflete qualidade real do módulo validado.
   - Estratégia: começar por escopo crítico já testado e expandir gradualmente.

4. **Mobile com token manual para validação rápida**
   - Motivo: acelerar teste E2E em ambiente de avaliação sem travar em fluxo completo de auth mobile.
   - Benefício: demonstração funcional rápida e controlada.

---

## 5) Problemas reais que apareceram e como foram resolvidos

## Problema A: erro 500 no proxy `/api/proxy/v1/conversations`
- Causa: frontend local apontando para hostname Docker (`backend-api`) fora da rede Docker.
- Correção: em execução local, usar `BACKEND_API_URL=http://localhost:8080`.

## Problema B: app mobile no Expo Go sem conectar API
- Causa: mobile usando `localhost` (do celular, não do PC).
- Correção: usar IP local da máquina no `.env` (`EXPO_PUBLIC_API_URL=http://SEU_IP:8080`).

## Problema C: CI falhando repetidamente na etapa de coverage backend
- Causa: combinação de driver/captura/parse de coverage inconsistente entre tentativas.
- Correção: hardening da etapa de geração e validação de coverage com checagens explícitas de artefato e parser robusto.

---

## 6) Como demonstrar rapidamente na entrevista (roteiro de demo)

1. Subir stack com Docker.
2. Mostrar login e chat web em duas abas (typing + mensagem real-time).
3. Mostrar OpenAPI/Swagger.
4. Mostrar app mobile no Expo Go enviando mensagem.
5. Mostrar pipeline CI com testes/cobertura.

Tempo estimado: 6-10 minutos.

---

## 7) Perguntas prováveis e respostas curtas

**"Por que removeu LLM?"**
> Porque o requisito mudou. Priorizamos foco no core do chat e reduzimos complexidade técnica/operacional.

**"Como garantiu qualidade?"**
> Com testes automatizados, cobertura mínima no CI e validação manual E2E web+mobile.

**"Qual foi o bug mais chato?"**
> A esteira de coverage no backend. Resolvi tratando causa raiz: geração confiável dos artefatos + validação robusta.

**"O que melhoraria se tivesse mais tempo?"**
> Expandir cobertura para mais módulos backend, formalizar auth no mobile sem token manual e adicionar observabilidade de latência/realtime.

---

## 8) Próximos passos sugeridos (pós-entrevista)

- Expandir testes backend para módulos além de `Support`.
- Consolidar estratégia de coverage por domínio.
- Implementar auth mobile completa (sem input manual de token).
- Criar script de bootstrap único (`make/dev.ps1`) para reduzir setup manual.

---

## 9) Frase de fechamento para entrevista

"O foco foi transformar um projeto de avaliação em algo demonstrável de ponta a ponta, com decisões pragmáticas, observando escopo, qualidade e previsibilidade de entrega."