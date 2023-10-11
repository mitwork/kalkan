FROM amazoncorretto:17-alpine

ARG VERSION=3.2.2

WORKDIR /app

RUN apk add curl unzip
RUN curl https://github.com/malikzh/NCANode/releases/download/$VERSION/NCANode-$VERSION.zip -L -o /tmp/NCANode.zip
RUN unzip /tmp/NCANode.zip -d /app && mv NCANode-$VERSION.jar NCANode.jar && chmod +x NCANode.jar

EXPOSE 14579

ENTRYPOINT ["java", "-jar", "NCANode.jar"]
