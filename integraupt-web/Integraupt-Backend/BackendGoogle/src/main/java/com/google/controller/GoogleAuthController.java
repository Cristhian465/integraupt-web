package com.google.controller;

import com.google.api.client.googleapis.auth.oauth2.GoogleAuthorizationCodeRequestUrl;
import com.google.config.GoogleConfig;
import com.google.dto.GoogleAuthUrlResponse;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/auth/google")
@CrossOrigin("*")
public class GoogleAuthController {

    private final GoogleConfig config;

    public GoogleAuthController(GoogleConfig config) {
        this.config = config;
    }

    @GetMapping("/login")
    public ResponseEntity<GoogleAuthUrlResponse> login() {

        GoogleAuthorizationCodeRequestUrl url
                = new GoogleAuthorizationCodeRequestUrl(
                        config.clientId,
                        config.redirectUri,
                        java.util.Arrays.asList("email", "profile")
                )
                        .setAccessType("offline")
                        .setApprovalPrompt("auto")
                        .set("include_granted_scopes", true);
        if (config.academicDomain != null && !config.academicDomain.isBlank()) {
            url.set("hd", config.academicDomain.trim());
        }

        return ResponseEntity.ok(
                new GoogleAuthUrlResponse(
                        true,
                        url.build(),
                        "Redirigiendo a Google para validar el correo institucional"
                )
        );
    }
}
