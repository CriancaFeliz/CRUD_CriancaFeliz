# Relatório de Higienização e Organização do Repositório

Data da conferência: 08/09/2026  
Base analisada: versão atual do Sistema Criança Feliz

## Objetivo

Registrar a organização adotada no repositório atual, em resposta à demanda de
higienização do projeto antigo. A conferência foi feita sem excluir arquivos
funcionais da versão atual.

## Resultado da auditoria da raiz

Não há scripts pontuais de correção, diagnósticos ou testes soltos na raiz
pública. A raiz mantém somente o ponto de entrada (`index.php`), configurações
de projeto, documentação geral, arquivos de container e pastas estruturais da
aplicação.

| Grupo | Localização atual | Justificativa |
| --- | --- | --- |
| Entrada da aplicação | `index.php` | Front Controller oficial. |
| Código MVC | `app/` | Controllers, Services, Models, Helpers, Config e Views. |
| Estilos, scripts e imagens | `css/`, `js/`, `img/`, `assets/` | Recursos públicos da interface. |
| Banco | `database/` | Setup oficial e documentação exclusiva do schema. |
| Manutenção pontual | `tools/maintenance/` | Criação de admin, correções históricas e instalações auxiliares. |
| Diagnósticos | `tools/diagnostics/` | Inspeções de estrutura, tabelas e depuração. |
| Legado | `tools/legacy/` e `docs/archive/` | Materiais preservados apenas para referência. |
| Testes | `tests/automated/`, `tests/integration/`, `tests/manual/` | Separação entre testes unitários, integração e manuais. |
| Dados de execução | `data/`, `uploads/`, `var/` | Dados locais, uploads e arquivos privados; não são fonte de código. |
| Documentação | `docs/` | Diagramas, manual, segurança, testes e decisões técnicas. |

## Proteções contra exposição web

O arquivo `.htaccess` bloqueia acesso direto a `app`, `tools`, `tests`,
`database`, `data`, `var`, `docker`, `docs`, `.git` e `.github`. Também bloqueia
arquivos de ambiente, SQL, logs, documentação e backups. Dessa forma, scripts
de manutenção e diagnóstico não ficam acessíveis pela URL pública.

## Decisões aplicadas à versão atual

1. `database/SETUP_COMPLETO_FINAL.sql` é a fonte única do schema para uma base
   nova.
2. Scripts SQL legados de migração foram retirados do fluxo atual; não devem
   ser executados sobre uma base com dados reais.
3. Dumps com dados pessoais permanecem fora do Git.
4. Arquivos de manutenção não fazem parte da navegação normal do sistema e são
   protegidos contra acesso web.

## Conclusão

A higienização solicitada para o repositório antigo já está refletida na base
atual: os arquivos estão classificados por responsabilidade e a raiz não
expõe scripts administrativos. A manutenção futura deve seguir a mesma regra:
nenhum script de correção, teste ou diagnóstico deve ser criado na raiz pública.
