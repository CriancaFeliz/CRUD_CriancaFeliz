# Correção do Cálculo de Renda Familiar

## Problema Identificado
A renda de Marina Carla estava incorreta: **R$ 360.000,00** quando deveria ser **R$ 3.500,00** (2000 + 1500).

## Causa Raiz
No arquivo `js/socioeconomico-multistep.js`, a função `consolidateAllSteps()` estava fazendo **dupla contagem**:
- Somava `renda_salario` + `renda_bolsa`
- MAIS a soma de todas as rendas da família em `familia_json`

Exemplo do erro:
```
renda_salario: 2000 (pai)
renda_bolsa: 1500 (mãe)
familia_json: [
  { nome: "João", renda: 2000 },  ← somava novamente
  { nome: "Maria", renda: 1500 }   ← somava novamente
]

Resultado: 2000 + 1500 + 2000 + 1500 = 7000 (errado!)
```

## Soluções Aplicadas

### 1. Corrigido `js/socioeconomico-multistep.js` (linhas 286-330)
- **Antes:** Somava TUDO (campos diretos + familia_json)
- **Depois:** Prioriza `familia_json` quando houver membros com renda
  - Se houver família com rendas → usa apenas a soma da família
  - Se NÃO houver → usa renda_salario + renda_bolsa

### 2. Melhorado `addFamilyMember()` (linhas 397-436)
- Adicionada conversão correta de renda (remove R$, pontos, converte vírgula)
- Adicionado log para debug (console mostra valores adicionados)

### 3. Corrigido cálculo de `renda_per_capita`
- Agora usa `familia.length` para dividir pela quantidade real de membros
- Anteriormente usava `num_comodos` ou `qtd_pessoas` (campos errados!)

## Página de Teste
Para testar o novo cálculo, acesse:
**`http://localhost/CriancaFeliz/debug_renda_calculation.php`**

Isto simula a adição de 2 membros (Pai R$2000, Mãe R$1500) e mostra o cálculo correto.

## Próximos Passos
1. ✅ Corrija a renda de Marina Carla: `http://localhost/CriancaFeliz/corrigir_renda_marina.php`
2. ✅ Ao reenviar o formulário, a nova lógica garantirá valores corretos
3. ✅ Teste com o debug: `debug_renda_calculation.php` e `debug_renda_list.php`

## Resumo das Mudanças
| Arquivo | Mudança | Motivo |
|---------|---------|--------|
| `js/socioeconomico-multistep.js` | Prioriza familia_json, evita dupla contagem | Evitar soma incorreta de rendas |
| `js/socioeconomico-multistep.js` | Melhora conversão de renda em addFamilyMember | Garantir números corretos |
| `js/socioeconomico-multistep.js` | usa familia.length para renda per capita | Dividir pelo número real de membros |
