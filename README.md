# Champs Fiscal

Componente PHP reutilizável para rotinas fiscais, com foco atual em **DC-e**, incluindo:

- montagem e normalização de requests
- validação de payloads
- assinatura XML
- transmissão SOAP
- autorização, consulta e cancelamento
- apoio para geração de DACE

O objetivo deste pacote é extrair a camada fiscal do projeto principal sem alterar o comportamento já existente, permitindo reutilização em múltiplas aplicações.

## Instalação

```bash
composer require betocampoy/champs-fiscal
```

## Dependências

Além das extensões nativas do PHP, o pacote depende de:

- `betocampoy/champs-certificate:^1.0`
- `endroid/qr-code:^6.0`
- `picqer/php-barcode-generator:^3.2`

Extensões obrigatórias:

- `ext-dom`
- `ext-libxml`
- `ext-openssl`
- `ext-soap`

## Namespace

```php
use BetoCampoy\Champs\Fiscal\...;
```

## Configuração de ambiente

Este pacote utiliza variáveis de ambiente no padrão abaixo. No momento, a leitura e a carga dessas variáveis continuam sendo responsabilidade da aplicação consumidora.

Isso funciona bem tanto em projetos Symfony quanto em projetos legados, desde que o carregamento do `.env` já esteja configurado pela aplicação.

Variáveis esperadas:

```dotenv
CHAMPS_FISCAL_DCE_EMISSION_TYPE='1'
CHAMPS_FISCAL_DCE_AUTHORIZER_SITE_NUMBER='0'
CHAMPS_FISCAL_DCE_ENVIRONMENT='2'
CHAMPS_FISCAL_DCE_APPLICATION_VERSION='MINHA ENCOMENDA'
CHAMPS_DCE_QRCODE_BASE_URL='https://dfe-portal.svrs.rs.gov.br/dce/QRCode'
```

### Recomendação importante

- mantenha no `.env` apenas valores base que podem ser versionados com segurança
- quando houver necessidade de sobrescrever valores por ambiente, prefira usar `.env.local`
- em especial, `CHAMPS_FISCAL_DCE_ENVIRONMENT` costuma ser um bom candidato para ficar em `.env.local`, evitando subir ajustes locais para o Git

Exemplo:

```dotenv
# .env
CHAMPS_FISCAL_DCE_EMISSION_TYPE='1'
CHAMPS_FISCAL_DCE_AUTHORIZER_SITE_NUMBER='0'
CHAMPS_FISCAL_DCE_APPLICATION_VERSION='MINHA ENCOMENDA'
CHAMPS_DCE_QRCODE_BASE_URL='https://dfe-portal.svrs.rs.gov.br/dce/QRCode'

# .env.local
CHAMPS_FISCAL_DCE_ENVIRONMENT='2'
```

Hoje o pacote **não obriga** automaticamente a existência das variáveis, mas já oferece uma classe utilitária para centralizar a leitura delas.

Exemplo de leitura:

```php
use BetoCampoy\Champs\Fiscal\Config\DceEnvironmentDefaults;

$defaults = DceEnvironmentDefaults::fromEnv();
```

## Exemplo de normalização de request

```php
use BetoCampoy\Champs\Fiscal\Config\DceEnvironmentDefaults;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Normalizer\DceAuthorizationRequestNormalizer;

$normalizer = new DceAuthorizationRequestNormalizer();
$request = $normalizer->normalize($request, DceEnvironmentDefaults::fromEnv());
```

## Exemplo de autorização

```php
use BetoCampoy\Champs\Fiscal\Dce\Authorization\DceAuthorizationService;

$service = new DceAuthorizationService(environment: 'homolog');
$response = $service->authorize($payload, $openedCertificateData);
```

## Estrutura

- `src/Dce/Authorization`: fluxo de autorização
- `src/Dce/Query`: fluxo de consulta
- `src/Dce/Event/Cancel`: fluxo de cancelamento
- `src/Dce/Dace`: apoio para impressão e mapeamento da DACE
- `src/Transmission`: transporte SOAP e DTOs
- `src/Xml`: assinatura XML
- `src/resources`: XSDs e WSDLs locais

## Observações

- Esta primeira versão foi componentizada sem refatorar regras de negócio existentes.
- Em uma próxima versão, pode valer adicionar uma validação explícita das variáveis de ambiente obrigatórias.
- O pacote foi preparado para publicação em GitHub e Packagist.

## Publicação

Exemplo de fluxo:

```bash
git init
git add .
git commit -m "Initial component release"
git tag v1.0.0
git push origin main --tags
```

Depois disso, basta apontar o repositório no Packagist e sincronizar a nova tag.

## Licença

MIT
