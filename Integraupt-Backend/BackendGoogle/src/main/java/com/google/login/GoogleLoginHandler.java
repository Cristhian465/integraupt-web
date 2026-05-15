package com.google.login;

import com.google.api.client.googleapis.auth.oauth2.*;
import com.google.api.client.http.javanet.NetHttpTransport;
import com.google.api.client.json.gson.GsonFactory;
import com.google.config.GoogleConfig;
import com.google.dto.GoogleCallbackResponse;
import com.google.dto.GoogleUserProfile;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/auth/google")
@CrossOrigin("*")
public class GoogleLoginHandler {

    private final GoogleConfig config;

    public GoogleLoginHandler(GoogleConfig config) {
        this.config = config;
    }

    @GetMapping("/callback")
    public ResponseEntity<GoogleCallbackResponse> callback(@RequestParam("code") String code) throws Exception {

        GoogleTokenResponse tokenResponse =
                new GoogleAuthorizationCodeTokenRequest(
                        new NetHttpTransport(),
                        new GsonFactory(),
                        config.clientId,
                        config.clientSecret,
                        code,
                        config.redirectUri
                ).execute();

        GoogleIdToken idToken = tokenResponse.parseIdToken();
        GoogleIdToken.Payload payload = idToken.getPayload();

        String email = payload.getEmail();
        boolean emailVerified = Boolean.TRUE.equals(payload.getEmailVerified());
        String hostedDomain = payload.getHostedDomain();
        String academicDomain = config.academicDomain != null ? config.academicDomain.trim() : null;

        if (!emailVerified) {
            return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(
                    new GoogleCallbackResponse(false, "El correo de Google no está verificado", null)
            );
        }

        if (academicDomain != null && !academicDomain.isBlank()) {
            boolean matchesDomain = false;

            if (hostedDomain != null) {
                matchesDomain = hostedDomain.equalsIgnoreCase(academicDomain);
            }

            if (!matchesDomain && email != null) {
                matchesDomain = email.toLowerCase().endsWith("@" + academicDomain.toLowerCase());
            }

            if (!matchesDomain) {
                return ResponseEntity.status(HttpStatus.FORBIDDEN).body(
                        new GoogleCallbackResponse(false, "Solo se permite el acceso con correo académico", null)
                );
            }
        }

        String fullName = (String) payload.get("name");
        String picture = (String) payload.get("picture");

        GoogleUserProfile profile = new GoogleUserProfile(
                email,
                fullName != null ? fullName : "",
                picture != null ? picture : "",
                hostedDomain,
                emailVerified
        );

        String frontendUrl = "http://localhost:5173/login?googleSuccess=true" +
            "&email=" + java.net.URLEncoder.encode(email, "UTF-8") +
            "&fullName=" + java.net.URLEncoder.encode(fullName != null ? fullName : "", "UTF-8") +
            "&picture=" + java.net.URLEncoder.encode(picture != null ? picture : "", "UTF-8");

        return ResponseEntity.status(HttpStatus.FOUND)
            .header("Location", frontendUrl)
            .build();
    }
}