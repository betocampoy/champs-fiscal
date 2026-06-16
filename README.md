# Champs Fiscal

Componente PHP reutilizável para rotinas fiscais brasileiras. Suporta:

| Documento | Operações |
|---|---|
| **DC-e** (Declaração de Conteúdo Eletrônica) | Emissão, consulta, cancelamento, DACE |
| **NFS-e Nacional** (Nota Fiscal de Serviço Eletrônica) | Emissão, consulta |

## Instalação

```bash
composer require betocampoy/champs-fiscal
```

## Requisitos

- PHP `>= 8.1`
- Extensões: `ext-dom`, `ext-libxml`, `ext-openssl`, `ext-soap`, `ext-mbstring`

## Dependências

- `betocampoy/champs-certificate` — abertura e leitura de certificados A1/A3
- `symfony/http-client` — transporte HTTP/REST para NFS-e Nacional
- `endroid/qr-code` — geração de QR Code para DACE
- `picqer/php-barcode-generator` — geração de código de barras para DACE

---

## DC-e

### Variáveis de ambiente

```dotenv
# .env
CHAMPS_FISCAL_DCE_EMISSION_TYPE='1'
CHAMPS_FISCAL_DCE_AUTHORIZER_SITE_NUMBER='0'
CHAMPS_FISCAL_DCE_APPLICATION_VERSION='MINHA APP'
CHAMPS_DCE_QRCODE_BASE_URL='https://dfe-portal.svrs.rs.gov.br/dce/QRCode'

# .env.local  (não versionar)
CHAMPS_FISCAL_DCE_ENVIRONMENT='2'   # 1=produção, 2=homologação
```

```php
use BetoCampoy\Champs\Fiscal\Config\DceEnvironmentDefaults;

$defaults = DceEnvironmentDefaults::fromEnv();
```

### Autorização

```php
use BetoCampoy\Champs\Fiscal\Dce\Authorization\DceAuthorizationService;

$service = new DceAuthorizationService(environment: 'homolog');

$response = $service->authorize($payload, $openedCertificateData);

if ($response->isSuccess()) {
    $chave = $response->getParsedValue('access_key');
}
```

### Consulta

```php
use BetoCampoy\Champs\Fiscal\Facade\FiscalDocumentQueryFacade;

$facade = new FiscalDocumentQueryFacade();
$response = $facade->queryByAccessKey($accessKey, $openedCertificate);
```

### Cancelamento

```php
use BetoCampoy\Champs\Fiscal\Dce\Event\Cancel\DceCancelService;

$service = new DceCancelService(environment: 'homolog');
$response = $service->cancel($payload, $openedCertificateData);
```

### Normalização de request

```php
use BetoCampoy\Champs\Fiscal\Config\DceEnvironmentDefaults;
use BetoCampoy\Champs\Fiscal\Dce\Request\Authorization\Normalizer\DceAuthorizationRequestNormalizer;

$normalizer = new DceAuthorizationRequestNormalizer();
$request = $normalizer->normalize($request, DceEnvironmentDefaults::fromEnv());
```

---

## NFS-e Nacional

Suporte à **NFS-e Nacional** (sistema federal obrigatório para municípios aderentes desde 2026).
Comunicação via REST + JSON com autenticação mTLS por certificado ICP-Brasil.

- Produção: `https://adn.nfse.gov.br`
- Homologação: `https://adn.producaorestrita.nfse.gov.br`

### Emissão

```php
use BetoCampoy\Champs\Fiscal\Nfse\Facade\NfseFacade;
use BetoCampoy\Champs\Fiscal\Nfse\Request\Input\NfseEmitRequest;
use BetoCampoy\Champs\Fiscal\Nfse\Request\Input\NfseProviderRequest;
use BetoCampoy\Champs\Fiscal\Nfse\Request\Input\NfseTakerRequest;
use BetoCampoy\Champs\Fiscal\Nfse\Request\Input\NfseTakerAddressRequest;
use BetoCampoy\Champs\Fiscal\Nfse\Request\Input\NfseServiceRequest;
use BetoCampoy\Champs\Fiscal\Nfse\Request\Input\NfseValuesRequest;

$facade = new NfseFacade(environment: 'homolog'); // ou 'prod'

$response = $facade->emit(
    request: new NfseEmitRequest(
        provider: new NfseProviderRequest(
            cnpj: '12345678000195',
            municipalRegistration: '12345',    // inscrição municipal
            emitterIbgeCode: '3515152',         // código IBGE do município do prestador
            simplesNacional: false,
        ),
        taker: new NfseTakerRequest(
            name: 'Empresa Cliente Ltda',
            cnpj: '98765432000111',             // ou cpf: '...'
            address: new NfseTakerAddressRequest(
                ibgeCode: '3550308',
                zipCode: '01310100',
                street: 'Av. Paulista',
                number: '1000',
                neighborhood: 'Bela Vista',
            ),
            email: 'financeiro@cliente.com.br',
        ),
        service: new NfseServiceRequest(
            nationalServiceCode: '16.01',        // código de tributação nacional
            municipalServiceCode: '1601',        // código do município
            description: 'Serviço de coleta e entrega de encomendas',
            serviceMunicipalityIbge: '3515152',  // IBGE do local de prestação
            cnae: '5320201',
        ),
        values: new NfseValuesRequest(
            serviceValue: 150.00,
            issAliquot: 0.02,    // 2% — verificar alíquota do município
            issRetained: false,
        ),
        rpsNumber: 1,
        rpsSeries: 'E',
    ),
    certificate: $openedCertificate,
);

if ($response->isSuccess()) {
    $chaveNfse = $response->getParsedValue('access_key');
    $numero    = $response->getParsedValue('number');
}
```

### Consulta

```php
$response = $facade->query($chaveNfse, $openedCertificate);
```

### Abertura do certificado

O parâmetro `$openedCertificate` é um `OpenedCertificateData` do pacote `betocampoy/champs-certificate`:

```php
use BetoCampoy\Champs\Certificate\Service\CertificateReaderService;

$reader = new CertificateReaderService();
$openedCertificate = $reader->readFromFile('/path/to/cert.pfx', 'senha');
```

---

## Estrutura do pacote

```
src/
├── Config/              Leitura de variáveis de ambiente (DC-e)
├── Dce/
│   ├── Authorization/   Emissão DC-e
│   ├── Query/           Consulta DC-e
│   ├── Event/Cancel/    Cancelamento DC-e
│   ├── Dace/            Impressão e mapeamento DACE
│   ├── Request/         Input, Builder, Mapper, Normalizer, Validator
│   ├── Response/        Parsers de resposta SOAP
│   ├── Transmission/    Transmissão SOAP + configuração
│   └── Signer/          Configuração de assinatura XML
├── Nfse/
│   ├── Authorization/   Emissão NFS-e (DPS → NFS-e)
│   ├── Query/           Consulta NFS-e
│   ├── Common/          DTOs compartilhados (Provider, Taker, Service, Values)
│   ├── Request/         Input e Payload builder
│   ├── Transmission/    Transmissão REST + configuração
│   └── Facade/          NfseFacade (entry point)
├── Facade/              FiscalDocumentQueryFacade (consulta DC-e)
├── Transmission/
│   ├── Contract/        DocumentTransmitterInterface
│   ├── Dto/             DocumentRequest, DocumentResponse, DocumentOperation
│   └── Transport/       SoapTransport, HttpTransport, credenciais TLS
├── Xml/                 XmlSigner, XmlSignatureConfig
├── ValueObject/         DfeAccessKey, DfeAccessKeyType
└── resources/           XSDs e WSDLs locais
```

---

## Uso em desenvolvimento (path local)

Para apontar para o pacote local sem publicar no Packagist, adicione ao `composer.json` do projeto consumidor:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../champs-fiscal",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "betocampoy/champs-fiscal": "@dev"
    }
}
```

Após rodar `composer update betocampoy/champs-fiscal`, o Composer criará um symlink para o diretório local — qualquer alteração no pacote é imediatamente refletida no projeto consumidor sem necessidade de reinstalar.

> Ajuste o `url` para o caminho relativo correto entre os dois projetos.

---

## Publicação de nova versão

```bash
git add .
git commit -m "feat: adiciona suporte a NFS-e Nacional"
git tag v2.0.0
git push origin main --tags
```

## Licença

MIT
