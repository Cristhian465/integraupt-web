package com.google.config;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Component;

@Component
public class GoogleConfig {

    @Value("${google.client-id}")
    public String clientId;

    @Value("${google.client-secret}")
    public String clientSecret;

    @Value("${google.redirect-uri}")
    public String redirectUri;
    
    @Value("${google.academic-domain}")
    public String academicDomain;
}
    