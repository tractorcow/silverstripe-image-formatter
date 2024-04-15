# Image formatter module

This module will let you transcode images on the fly in your templates.

## Requirements

* Silverstripe 5.2+

## Installation Instructions

* Install using composer

```bash
composer require tractorcow/silverstripe-image-formatter
```

## Usage

```html

<div>
    $HeaderImage.Format('webp').Fill(250, 250)
</div>
```

Conversions to `webp` and `avif` use a default quality setting of `75` and `50` respectively. You can update the 
quality used via config:

```yaml
TractorCow\SilverStripeImageFormatter\ImageFormatExtension:
  default_quality: 80
  jpg_quality: 80
  webp_quality: 75
  avif_quality: 50
```

(The conversion documented [here](https://docs.silverstripe.org/en/5/developer_guides/files/file_manipulation/#converting-between-image-formats)
leaves the quality of the generated images as is, which, at the standard quality of 90, results in larger images 
for webp and avif, which defeats the purpose of these formats.)

## Disclaimer

Prior to Silverstripe 5.2, this module did override some internal core behaviour in order to support image 
formatting. All formatted images were public by default!

Since Silverstripe 5.2 it uses an improved version of the [example from the documentation](https://docs.silverstripe.org/en/5/developer_guides/files/file_manipulation/#converting-between-image-formats) 
to convert images between formats.

## License

Copyright (c) 2021, Damian Mooyman

All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright
  notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright
  notice, this list of conditions and the following disclaimer in the
  documentation and/or other materials provided with the distribution.
* The name of Damian Mooyman may not be used to endorse or promote products
  derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
