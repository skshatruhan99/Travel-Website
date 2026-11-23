@extends('layouts.app')

@section('title', 'Contact & Support')

@section('content')

<section class="page-hero">

    <div class="container">

        <h1>Contact & Support</h1>

        <p>
            Have a question? Our team is ready to help.
        </p>

    </div>

</section>


<section class="section">

    <div class="container">

        <div class="contact-grid">

            <div class="contact-info">

                <h2>Let's Talk</h2>

                <p>
                    Contact our travel support team for help with
                    bookings, tours or anything else.
                </p>


                <div class="contact-item">

                    <i class="fa-solid fa-location-dot"></i>

                    <div>
                        <strong>Office</strong>
                        <p>Pokhara, Nepal</p>
                    </div>

                </div>


                <div class="contact-item">

                    <i class="fa-solid fa-envelope"></i>

                    <div>
                        <strong>Email</strong>
                        <p>support@npadvanture.com</p>
                    </div>

                </div>


                <div class="contact-item">

                    <i class="fa-solid fa-phone"></i>

                    <div>
                        <strong>Phone</strong>
                        <p>+977 9800000000</p>
                    </div>

                </div>

            </div>


            <div class="booking-card">

                <h2>Send Us a Message</h2>

                @if(session('success'))
                    <p class="badge badge-green">{{ session('success') }}</p>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" style="margin-top:25px">
                    @csrf

                    <div class="form-grid">

                        <div class="form-group">

                            <label for="contact_name">Name</label>

                            <input id="contact_name" name="name" type="text"
                                   class="form-control"
                                   placeholder="Your name"
                                   required>

                        </div>


                        <div class="form-group">

                            <label for="contact_email">Email</label>

                            <input id="contact_email" name="email" type="email"
                                   class="form-control"
                                   placeholder="Your email"
                                   required>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="contact_subject">Subject</label>

                        <input id="contact_subject" name="subject" type="text"
                               class="form-control"
                               placeholder="How can we help?"
                               required>

                    </div>


                    <div class="form-group">

                        <label>Message</label>

                        <textarea id="contact_message" name="message" class="form-control"
                                  rows="6"
                                  placeholder="Write your message..."
                                  required></textarea>

                    </div>


                    <button type="submit"
                            class="btn btn-primary">
                        Send Message
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>

                </form>

            </div>

        </div>

    </div>

</section>

@endsection