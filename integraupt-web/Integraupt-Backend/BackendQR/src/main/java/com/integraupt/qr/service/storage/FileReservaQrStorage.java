package com.integraupt.qr.service.storage;

import java.io.IOException;
import java.io.ObjectInputStream;
import java.io.ObjectOutputStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardCopyOption;
import java.util.Locale;
import java.util.Map;
import java.util.Objects;
import java.util.Optional;
import java.util.concurrent.ConcurrentHashMap;
import java.util.stream.Stream;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Component;

import com.integraupt.qr.model.ReservaQrData;

@Component
public class FileReservaQrStorage implements ReservaQrStorage {

    private static final Logger LOGGER = LoggerFactory.getLogger(FileReservaQrStorage.class);
    private static final String FILE_EXTENSION = ".qr";

    private final Map<String, ReservaQrData> cache = new ConcurrentHashMap<>();
    private final Path storageDirectory;

    public FileReservaQrStorage(@Value("${app.qr-storage.directory:./data/qr-reservas}") String directory) {
        this.storageDirectory = Path.of(directory).toAbsolutePath().normalize();
        try {
            Files.createDirectories(storageDirectory);
            loadExistingFiles();
        } catch (IOException ex) {
            throw new IllegalStateException("No se pudo inicializar el almacenamiento de códigos QR", ex);
        }
    }

    @Override
    public void save(ReservaQrData data) {
        String key = normalizeKey(data != null ? data.getToken() : null);
        if (key == null || data == null) {
            return;
        }

        cache.put(key, data);
        Path tempFile = null;
        try {
            String prefix = key.length() >= 3 ? key : ("qr-" + key);
            tempFile = Files.createTempFile(storageDirectory, prefix, FILE_EXTENSION + ".tmp");
            try (ObjectOutputStream outputStream = new ObjectOutputStream(Files.newOutputStream(tempFile))) {
                outputStream.writeObject(data);
                outputStream.flush();
            }

            Path target = resolvePath(key);
            try {
                Files.move(tempFile, target, StandardCopyOption.REPLACE_EXISTING, StandardCopyOption.ATOMIC_MOVE);
            } catch (IOException atomicEx) {
                Files.move(tempFile, target, StandardCopyOption.REPLACE_EXISTING);
            }
        } catch (IOException ex) {
            LOGGER.warn("No se pudo guardar el código QR {}: {}", key, ex.getMessage());
            if (tempFile != null) {
                try {
                    Files.deleteIfExists(tempFile);
                } catch (IOException cleanupEx) {
                    LOGGER.debug("No se pudo eliminar el archivo temporal {}: {}", tempFile, cleanupEx.getMessage());
                }
            }
        }
    }

    @Override
    public Optional<ReservaQrData> findByToken(String token) {
        String key = normalizeKey(token);
        if (key == null) {
            return Optional.empty();
        }

        ReservaQrData cached = cache.get(key);
        if (cached != null) {
            return Optional.of(cached);
        }

        Path file = resolvePath(key);
        if (!Files.exists(file)) {
            return Optional.empty();
        }

        try (ObjectInputStream inputStream = new ObjectInputStream(Files.newInputStream(file))) {
            Object stored = inputStream.readObject();
            if (stored instanceof ReservaQrData data) {
                cache.put(key, data);
                return Optional.of(data);
            }
        } catch (IOException | ClassNotFoundException ex) {
            LOGGER.warn("No se pudo leer el código QR {}: {}", key, ex.getMessage());
        }

        return Optional.empty();
    }

    @Override
    public Optional<ReservaQrData> findByReservaId(Long reservaId) {
        if (reservaId == null) {
            return Optional.empty();
        }

        return cache.values().stream()
                .filter(data -> data.getReserva() != null
                        && reservaId.equals(data.getReserva().getReservaId()))
                .findFirst();
    }

    private void loadExistingFiles() {
        try (Stream<Path> files = Files.list(storageDirectory)) {
            files.filter(Files::isRegularFile)
                 .filter(path -> path.getFileName().toString().endsWith(FILE_EXTENSION))
                 .forEach(this::loadFile);
        } catch (IOException ex) {
            LOGGER.warn("No se pudieron cargar los códigos QR almacenados: {}", ex.getMessage());
        }
    }

    private void loadFile(Path path) {
        try (ObjectInputStream inputStream = new ObjectInputStream(Files.newInputStream(path))) {
            Object stored = inputStream.readObject();
            if (stored instanceof ReservaQrData data) {
                String key = normalizeKey(data.getToken());
                if (key != null) {
                    cache.putIfAbsent(key, data);
                }
            }
        } catch (IOException | ClassNotFoundException ex) {
            LOGGER.warn("El archivo {} no se pudo cargar: {}", path.getFileName(), ex.getMessage());
        }
    }

    private Path resolvePath(String key) {
        String safeName = key.replaceAll("[^a-zA-Z0-9._-]", "_");
        if (safeName.length() > 200) {
            safeName = safeName.substring(0, 200);
        }
        return storageDirectory.resolve(safeName + FILE_EXTENSION);
    }

    private String normalizeKey(String token) {
        if (Objects.isNull(token)) {
            return null;
        }
        String trimmed = token.trim();
        if (trimmed.isEmpty()) {
            return null;
        }
        return trimmed.toLowerCase(Locale.ROOT);
    }
}
