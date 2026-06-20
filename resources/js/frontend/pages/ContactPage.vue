<template>
    <section class="section-padding">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <p class="text-primary text-uppercase fw-semibold">Contact</p>
                    <h1 class="display-5 fw-bold">Let’s talk about what you need</h1>
                    <p class="lead text-secondary">Tell us a little about your business and we’ll get back to you soon.</p>
                </div>
                <div class="col-lg-7">
                    <form class="card border-0 shadow-sm" @submit.prevent>
                        <div class="card-body p-4 p-md-5">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Name</label>
                                    <input id="name" type="text" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input id="email" type="email" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea id="message" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit">Send message</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>
