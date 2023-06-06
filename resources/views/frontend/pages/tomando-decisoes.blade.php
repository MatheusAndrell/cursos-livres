@extends('frontend.layouts.app' . config('theme_layout'))

@section('title', $page->meta_title ? $page->meta_title : app_name())
@section('meta_description', $page->meta_description ? $page->meta_description : '')
@section('meta_keywords', $page->meta_keywords ? $page->meta_keywords : app_name())


@section('content')

    <link rel="stylesheet" href="/css/frontendgold.css">

    <!-- Start navbar
                                 ============================================= -->
    <section class="sectionnav">
        <nav class="navgold">
            <a class="agold" href="{{ url('/') }}">Home</a>
            <a class="agold" href="{{ url('bundles') }}">Vitrine</a>
            <a class="agold" href="{{ url('contact') }}">Contato</a>
        </nav>
    </section>
    <!-- End navbar
                                             ============================================= -->

    <!-- Start of banner
                                 ============================================= -->
    <section class="gold">
        @if($page->image != "")
            <div class="responsive-image">
                <img src="{{asset('storage/uploads/'.$page->image)}}" alt="">
            </div>
    @endif
    </section>

    <!-- End of banner
                                ============================================= -->

    <!-- Start of about-page section
                                ============================================= -->
    <section id="about-page" class="about-page-section-gold">
        <div class="container-platinum">
            <div class="@if ($page->sidebar == 1) col-md-9 @else col-md-12 @endif ">
                <div class="about-us-content-item">

                    <!-- /gallery -->

                    <div class="about-text-item-gold">
                        <div class="section-title-2-gold  headline text-left">
                            <h2>{{ $page->title }}</h2>
                        </div>
                        {!! $page->content !!}
                    </div>
                    <!-- /about-text -->
                </div>
            </div>
            @if ($page->sidebar == 1)
                @include('frontend.layouts.partials.right-sidebar')
            @endif
        </div>
        </div>
    </section>
    <!-- End of about-page section
                                        ============================================= -->
    <!-- Start of container-card
                                        ============================================= -->
    <section>
        <div class="container-card-gold stars-gold">
            <div class="card-gold">
                <div class="img">
                    <span>Profissionais capacitados</span>
                </div>
                <div class="content">

                    <p class="desc-card-gold">Uma das vantagens de escolher o nosso curso é que ele oferece uma formação
                        completa
                        e
                        atualizada, com foco nas demandas e tendências do mercado de trabalho. Os profissionais que se
                        formam em nosso curso estão aptos a lidar com os desafios do mundo corporativo e a desenvolver
                        soluções criativas e eficientes para os problemas das empresas.</p>
                </div>
                <div class="arrow">
                    <span>&#8673;</span>
                </div>
            </div>
            <div class="card-gold">
                <div class="img">
                    <span>Inovação e tecnologia</span>
                </div>
                <div class="content">

                    <p class="desc-card-gold">Outra vantagem é a ênfase na inovação e tecnologia, que são fundamentais para
                        o
                        sucesso das organizações nos dias de hoje. Os alunos do nosso curso têm acesso a recursos
                        tecnológicos
                        de última geração, além de estarem imersos em um ambiente de inovação e criatividade, que estimula a
                        criação de soluções inovadoras.</p>
                </div>
                <div class="arrow">
                    <span>&#8673;</span>
                </div>
            </div>
            <div class="card-gold">
                <div class="img">
                    <span>Excelência acadêmica</span>
                </div>
                <div class="content">

                    <p class="desc-card-gold">Uma terceira vantagem é a possibilidade de formar uma rede de contatos de
                        grande
                        valor
                        para a sua carreira. Durante o curso, os alunos têm a oportunidade de interagir com colegas,
                        professores e profissionais do mercado, criando laços e estabelecendo conexões que podem abrir
                        portas para oportunidades de emprego e parcerias futuras.</p>
                </div>
                <div class="arrow">
                    <span>&#8673;</span>
                </div>
            </div>
        </div>
    </section>
    <!-- End of container-card
                                    ============================================= -->
    <!-- Start of element-section
                                    ============================================= -->
    <section class="element-section-gold">
        <div class="element-gold">
            <div class="title-element-gold">
                <p class="p-element-gold">O que temos de especial que ninguém mais tem</p>
            </div>
            <div class="cards-container">
                <div class="element-card-gold">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <div class="card-content-gold">
                        <h2>Conteúdo de qualidade</h2>
                        <p> Nosso site de curso oferece um conteúdo de qualidade, que é projetado para ser fácil de entender
                            e aplicar na vida real. Isso significa que os alunos poderão aprender de forma eficiente e
                            aplicar o que aprenderam em seu trabalho ou vida pessoal.</p>
                    </div>
                </div>
                <div class="element-card-gold">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <div class="card-content-gold">
                        <h2>Professores experientes</h2>
                        <p> Nossos professores são altamente experientes e qualificados em suas áreas de especialização.
                            Eles
                            têm uma ampla gama de habilidades, experiência prática e conhecimentos para compartilhar com os
                            alunos.</p>
                    </div>
                </div>
                <div class="element-card-gold">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <div class="card-content-gold">
                        <h2>Flexibilidade de horários</h2>
                        <p> Nosso site de curso oferece flexibilidade de horários, permitindo que os alunos estudem em seu
                            próprio ritmo e horário conveniente. Isso é especialmente útil para aqueles que têm trabalhos
                            diários ou outras responsabilidades que tornam difícil seguir uma programação rígida.</p>
                    </div>
                </div>
            </div>
    </section>
    <!-- End of element-section
                                   ============================================= -->
    <!-- Start of subscribe
                                   ============================================= -->
    <section class="container-form-gold">
        <section>
            <div class="content-form-gold">
                <div class="form-title-gold">
                    <h2>Matricule-se agora</h2>
                </div>
                <div class="form-x-gold">
                    <h2>12x</h2>
                </div>
                <div class="form-price-gold">
                    <h2>999</h2>
                </div>
                <div class="form-2-gold">
                    <h2>,99</h2>
                </div>
                <div class="form-info-gold">
                    <h2>ou {{$appCurrency['symbol'].' '. $bundle->price }} à vista</h2>
                </div>
                <a href="{{ route('cart.index') }}" role="button-gold">
                    <div class="form-button-gold">
                        <span><b>QUERO PAGAR COM CARTÃO
                                <br>
                                DE CRÉDITO</b> - EM ATÉ 12X</span>
                    </div>
                </a>
                <a href="{{ route('cart.index') }}" role="button">
                    <div class="form-button-gold2">
                        <span><b>QUERO PAGAR COM BOLETO
                                <br>
                                OU PIX</b> - À VISTA</span>
                    </div>
                </a>
            </div>
        </section>
    </section>

    <!-- End of subscribe
                                   ============================================= -->
    <!-- Start of course
                                   ============================================= -->
    <section class="ofcourse-gold">
        <div class="titlecourse-gold">
            <h2> O que você vai aprender?</h2>
        </div>
        <div class="modules-gold">
            <ul>
                <li>
                    <button onclick="toggleSubmodules(1)">Módulo 01 - Fundamentos do Front-end </button>
                    <ul class="submodule-1" style="display:none">
                        <li>Aula &nbsp 1: Introdução ao desenvolvimento Front-end</li>
                        <li>Aula &nbsp 2: HTML - Estruturação de páginas web</li>
                        <li>Aula &nbsp 3: CSS - Estilização de páginas web</li>
                        <li>Aula &nbsp 4: JavaScript - Programação básica</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(2)">Módulo 02 - Avançando no Front-end</button>
                    <ul class="submodule-2" style="display:none">
                        <li>Aula &nbsp 1: Frameworks CSS</li>
                        <li>Aula &nbsp 2: JavaScript - Programação intermediária</li>
                        <li>Aula &nbsp 3: jQuery - Biblioteca JavaScript</li>
                        <li>Aula &nbsp 4: Responsividade e Design Responsivo</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(3)">Módulo 03 - Frameworks Front-end </button>
                    <ul class="submodule-3" style="display:none">
                        <li>Aula &nbsp 1: Introdução aos Frameworks Front-end</li>
                        <li>Aula &nbsp 2: Angular - Framework da Google</li>
                        <li>Aula &nbsp 3: React - Biblioteca de Componentes</li>
                        <li>Aula &nbsp 4: Vue - Framework progressivo</li>
                        <li>Aula &nbsp 4: Integração de API's</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(4)">Módulo 04 - Projetos e Exercícios finais</button>
                    <ul class="submodule-4" style="display:none">
                        <li>Aula &nbsp 1: Projeto Final - Criação de um Site</li>
                        <li>Aula &nbsp 2: Desafios para praticar </li>
                    </ul>
                </li>
            </ul>
            <script>
                function toggleSubmodules(moduleIndex) {
                    var submodules = document.getElementsByClassName('submodule-' + moduleIndex);
                    for (var i = 0; i < submodules.length; i++) {
                        submodules[i].style.display = submodules[i].style.display === 'none' ? 'block' : 'none';
                    }
                }
            </script>

    </section>


    <!-- End of course
                                 ============================================= -->

    <!-- Start Contacts
                                 ============================================= -->
    <section class="contact-gold">
        <div class="contact-container-gold">
            <span class="big-circle-gold"></span>
            <img src="/img/shape.png" class="square" alt="" />
            <div class="form">
                <div class="contact-infoo-gold">
                    <h3 class="title">Tem alguma dúvida?</h3>
                    <p class="text-gold">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Saepe
                        dolorum adipisci recusandae praesentium dicta!
                    </p>

                    <div class="info">
                        <div class="information">
                            <img src="/img/location.png" class="icon" alt="" />
                            <p>92 Cherry Drive Uniondale, NY 11553</p>
                        </div>
                        <div class="information">
                            <img src="/img/email.png" class="icon" alt="" />
                            <p>lorem@ipsum.com</p>
                        </div>
                        <div class="information">
                            <img src="/img/phone.png" class="icon" alt="" />
                            <p>123-456-789</p>
                        </div>
                    </div>

                    <div class="social-mediaa-gold">
                        <p>Nossas redes-sociais :</p>
                        <div class="social-iconss-gold">
                            <a href="#">
                                <i class="fab fa-facebook-f sm"></i>
                            </a>
                            <a href="#">
                                <i class="fab fa-twitter sm"></i>
                            </a>
                            <a href="#">
                                <i class="fab fa-instagram sm"></i>
                            </a>
                            <a href="#">
                                <i class="fab fa-whatsapp sm"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="contact-form-gold">
                    <span class="circle-gold one"></span>
                    <span class="circle-gold two"></span>

                    <form action="cc" autocomplete="off">
                        <h3 class="title">Contate-nos</h3>
                        <div class="input-container-gold">
                            <input type="text" name="name" class="input" />
                            <label for="">Nome</label>
                            <span>Nome</span>
                        </div>
                        <div class="input-container-gold">
                            <input type="email" name="email" class="input" />
                            <label for="">Email</label>
                            <span>Email</span>
                        </div>
                        <div class="input-container-gold">
                            <input type="tel" name="phone" class="input" />
                            <label for="">Telefone</label>
                            <span>Telefone</span>
                        </div>
                        <div class="input-container-gold textarea">
                            <textarea name="message" class="input"></textarea>
                            <label for="">Mensagem</label>
                            <span>Mensagem</span>
                        </div>
                        <input type="submit" value="Enviar" class="btnn-gold" />
                    </form>
                </div>
            </div>
        </div>
        <script src="/js/form.js"></script>
    </section>

    <!-- End Contacts
                                 ============================================= -->
    <!-- Start Footer
                                 ============================================= -->
    <footer class="gold-footer">
        <div id="footer_content">
            <div id="footer_contacts">
                <h1>Logo</h1>
                <p>It's all about your dreams.</p>

                <div id="footer_social_media">
                    <a href="#" class="footer-link" id="instagram">
                        <i class="fab fa-instagram"></i>
                    </a>

                    <a href="#" class="footer-link" id="facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>

                    <a href="#" class="footer-link" id="whatsapp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <ul class="footer-list">
                <li>
                    <h3>Blog</h3>
                </li>
                <li>
                    <a href="#" class="footer-link">Tech</a>
                </li>
                <li>
                    <a href="#" class="footer-link">Adventures</a>
                </li>
                <li>
                    <a href="#" class="footer-link">Music</a>
                </li>
            </ul>

            <ul class="footer-list">
                <li>
                    <h3>Products</h3>
                </li>
                <li>
                    <a href="#" class="footer-link">App</a>
                </li>
                <li>
                    <a href="#" class="footer-link">Desktop</a>
                </li>
                <li>
                    <a href="#" class="footer-link">Cloud</a>
                </li>
            </ul>

            <div id="footer_subscribe">
                <h3>Subscribe</h3>

                <p>
                    Enter your e-mail to get notified about
                    our news solutions
                </p>

                <div id="input_group">
                    <input type="email" id="email">
                    <button>
                        <i class="fas fa-mail-bulk"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="footer_copyright">
            &#169
            2023 todos os direitos reservados
        </div>
    </footer>
    <!-- End Footer
                                 ============================================= -->
