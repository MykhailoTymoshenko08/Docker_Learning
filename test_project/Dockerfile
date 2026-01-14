

FROM openjdk:27-ea-trixie
WORKDIR /java
COPY Main.java .
RUN javac Main.java
EXPOSE 8001
CMD ["java", "Main"]


# javac = java compiler give us to launch some code
# ["java, Main"] = java.main

