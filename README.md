# Cropper by Renato Montanari

[![Maintainer](https://img.shields.io/badge/maintainer-@renatomontanari-blue.svg?style=flat-square)](https://informaticalivre.com.br)
[![Source Code](https://img.shields.io/badge/source-renatomontanari/cropper-blue.svg)](https://github.com/renatomontanari/cropper)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/renatomontanari/cropper.svg?style=flat-square)](https://packagist.org/packages/renatomontanari/cropper)
[![Latest Version](https://img.shields.io/github/release/renatomontanari/cropper.svg?style=flat-square)](https://github.com/informaticalivreoficial/cropper/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/renatomontanari/cropper.svg?style=flat-square)](https://packagist.org/packages/renatomontanari/cropper)

###### Smart Cropper is a modern PHP image cropping and thumbnail generation library with built-in caching and WebP support.

Cropper é um componente que simplifica a criação de miniaturas de imagens JPG, PNG e WebP com um motor de cache inteligente. O Cropper cria versões de suas imagens para cada dimensão necessária na aplicação com zero de complexidade.

---

### Highlights

- Simple Thumbnail Creator (Simples criador de miniaturas)
- Cache optimization per dimension (Otimização em cache por dimensão)
- WebP output by default (Saída em WebP por padrão)
- Media Control by Filename (Controle de mídias por nome do arquivo)
- Cache cleanup by filename and total (Limpeza de cache por nome de arquivo e total)
- Composer ready and PSR-2 compliant (Pronto para o composer e compatível com PSR-2)

---

## Requisitos

- PHP >= 8.0
- Extensão GD habilitada
- Composer

---

## Instalação

Cropper está disponível via Composer:
```bash
composer require renatomontanari/cropper
```

Ou adicione manualmente no `composer.json`:
```json
"require": {
    "renatomontanari/cropper": "2.0.*"
}
```

---

## Documentação

São apenas dois métodos para fazer todo o trabalho. Você só precisa chamar o **`make`** para criar ou usar miniaturas de qualquer tamanho, ou o **`flush`** para liberar o cache de um arquivo ou da pasta toda.

#### Criar miniaturas
```php
<?php

use Renato\Cropper\Cropper;

$c = new Cropper("path/to/cache");

// Somente largura (altura proporcional)
echo "<img src='{$c->make("images/image.jpg", 500)}' alt='Imagem'>";

// Largura e altura definidas
echo "<img src='{$c->make("images/image.jpg", 500, 300)}' alt='Imagem'>";
```

#### Limpar cache
```php
<?php

use Renato\Cropper\Cropper;

$c = new Cropper("path/to/cache");

// Limpar cache de um arquivo específico
$c->flush("images/image.jpg");

// Limpar todo o cache
$c->flush();
```

#### Uso com Laravel
```php
// No AppServiceProvider ou diretamente no controller
$cropper = new \Renato\Cropper\Cropper(storage_path('app/public/cache'));

$url = $cropper->make('images/image.jpg', 800, 600);
```

---

## WebP

A partir da versão `2.0.*` todas as miniaturas são geradas automaticamente em formato **WebP**, garantindo melhor performance e menor tamanho de arquivo sem perda de qualidade visível.

---

## Contribuindo

Por favor, veja [CONTRIBUTING](CONTRIBUTING.md) para mais detalhes.

---

## Segurança

Se você descobrir algum problema relacionado à segurança, envie um e-mail para **suporte@informaticalivre.com.br** em vez de usar o rastreador de problemas.

---

## Créditos

- [Robson V. Leite](https://github.com/robsonvleite) (Desenvolvedor original)
- [Renato Montanari](https://github.com/informaticalivreoficial) (Mantenedor atual)

---

## Licença

The MIT License (MIT). Veja [License File](LICENSE) para mais informações.