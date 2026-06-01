# Setup SMTP para Recuperacao de Senha

Atualizado em 2026-06-01.

O fluxo de recuperacao ja cria tokens com hash em `password_reset_tokens`, mas o envio real ainda nao esta ligado a um provedor SMTP. Enquanto isso, em desenvolvimento, a URL de reset aparece no log do PHP.

## O Que Falta Decidir

| Item | Por que falta | Precisa resolver |
| --- | --- | --- |
| Provedor | Cada provedor muda host, porta, TLS e politica antispam | Escolher Gmail Workspace, Microsoft 365, SendGrid, Mailgun, Amazon SES ou outro |
| Remetente | O dominio/remetente precisa ser autorizado | Definir email oficial, exemplo `nao-responda@dominio.org.br` |
| Credenciais | Nao podem ficar no repo | Criar senha de app/API key e guardar em cofre/variavel do servidor |
| Ambiente de teste | Nao e seguro testar em emails reais de atendidos | Criar sandbox/lista de teste |
| Conteudo do email | Precisa aprovar texto, assinatura e prazo do link | Definir modelo institucional |

## Variaveis Recomendadas

Quando o envio for implementado, usar variaveis de ambiente, nunca valores fixos no codigo:

```env
SMTP_HOST=smtp.exemplo.com
SMTP_PORT=587
SMTP_USER=usuario
SMTP_PASS=senha-ou-api-key
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=nao-responda@exemplo.org.br
SMTP_FROM_NAME=Associacao Crianca Feliz
SMTP_TIMEOUT=10
```

## Checklist de Producao

1. Validar SPF, DKIM e DMARC do dominio.
2. Usar TLS na porta 587 ou SMTPS na porta 465.
3. Guardar credenciais fora do Git.
4. Registrar apenas hash do email ou metadados minimos em logs.
5. Testar reset completo em conta de homologacao.
6. Ativar monitoramento de falhas de envio.

## Teste Que Deve Ser Criado Depois

Quando houver sandbox SMTP:

1. pedir reset para usuario de teste;
2. confirmar token criado em `password_reset_tokens`;
3. capturar email no sandbox;
4. abrir link recebido;
5. redefinir senha;
6. confirmar token marcado como usado;
7. confirmar login com nova senha.
