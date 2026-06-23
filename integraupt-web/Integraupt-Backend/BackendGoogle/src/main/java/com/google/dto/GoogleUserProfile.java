package com.google.dto;

public record GoogleUserProfile(
        String email,
        String fullName,
        String picture,
        String hostedDomain,
        boolean emailVerified
) {
}