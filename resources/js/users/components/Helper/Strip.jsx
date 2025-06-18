import { Title, ScrollArea, Group, Box } from "@mantine/core";

export function Strip({
  id,
  nav = "scroll",
  gradientValues = [],
  title,
  iconColor = "white",
  className = "",
  children
}) {
  return (
    <Box id={id} className={`mb-8 ${className}`}>
      {/* Header Title */}
      <Title
        order={3}
        style={{
          background: `linear-gradient(90deg, ${gradientValues.join(", ")})`,
          WebkitBackgroundClip: "text",
          WebkitTextFillColor: "transparent"
        }}
        mb="sm"
      >
        {title}
      </Title>

      {/* Scrollable Content */}
      <ScrollArea type="always" scrollHideDelay={0}>
        <Group spacing="md" noWrap>
          {children}
        </Group>
      </ScrollArea>
    </Box>
  );
}
